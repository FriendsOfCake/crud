<?php
namespace Crud\Routing\Route;

use Cake\Database\Exception;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use stdClass;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiRoute extends Route
{

    /**
     * Parse method.
     *
     * @param string $url The URL to parse
     * @param string $method The HTTP method of the request being parsed
     * @return mixed URL parameter array on success, false otherwise
     */
    public function parse($url, $method = '')
    {
        $params = $this->_belongsToRelationshipSelfLink($url);
        if (is_array($params)) {
            return $params;
        }

        return false;
    }

    /**
     * Detects JSON API relationship `self` links with belongsTo relationship.
     *
     * Please note that by design a JSON API relationship `self` link with a
     * belongsTo relationship does NOT contain a foreign key/id since they are
     * intended to always point to the actual related record.
     *
     * Valid URLs:
     * http://my.app/countries/3/relationships/currency
     * http://my.app/countries/3/relationships/currency?query=parameter
     *
     * In the above example we would first query the database for a country
     * record with `id` 3 If found, we extract the foreign key field
     * `currency_id` (e.g. 12) and return a `$params` array pointing to
     * CurrenciesController with action `view` and `id` 12.
     *
     * BTW: query parameters are caught by the regex but not processed (yet).
     *
     * @param string $url URL to parse
     * @return mixed bool|array Params array for matching URLs, false otherwise
     */
    protected function _belongsToRelationshipSelfLink($url)
    {
        $url = $this->_getUrlObject($url);
        if (!$url) {
            return false;
        };

        if ($url->relationship !== 'belongsTo') {
            return false;
        }

        // try fetching parent resource from the database
        $table = TableRegistry::get($url->parentController);

        try {
            $result = $table
                ->find()
                ->where([
                    'id' => $url->parentId
                ])
                ->first();
        } catch (Exception $e) {
            return false;
        }

        // no further action if main record or foreign key does not exist
        if (empty($result)) {
            return false;
        }

        if (!isset($result[$url->relationshipForeignKeyField])) {
            return false;
        }

        // all good, return params to redirect user to related record
        return [
            'controller' => $url->relationshipController,
            'action' => 'view',
            'pass' => [
                $result[$url->relationshipForeignKeyField]
            ]
        ];
    }

    /**
     * Returns an object with detailed analysis properties for any given URL
     * if it matches one of the supported JSON API link formats. Properties:
     *
     * [url] => /countries/2/relationships/currency
     * [scheme] =>
     * [authority] =>
     * [path] => /countries/2/relationships/currency
     * [query] =>
     * [parentPath] => countries
     * [parentController] => Countries
     * [parentId] => 2
     * [relationship] => belongsTo
     * [relationshipController] => Currencies
     * [relationshipForeignKeyField] => currency_id
     *
     * @param string $url URL to analyse
     * @return bool|stdClass False if the regex did not match.
     */
    protected function _getUrlObject($url)
    {
        $object = new stdClass();

        // Parse URI as described at https://tools.ietf.org/html/rfc3986#appendix-B
        $regex = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';

        preg_match($regex, $url, $matches);

        $object->url = $url;
        $object->scheme = $matches[2];
        $object->authority = $matches[4];
        $object->path = $matches[5];

        $object->query = null;
        if (isset($matches[7])) {
            $object->query = $matches[7];
        }

        //
        // Regex if path matches a valid JSON API relationship `self` link:.
        //
        // $1 parentPath (e.g. /prefix/controller/action
        // $2 parentId (e.g. 1)
        // $4 relationship (either singular or plural)
        //
        $regex = '/\/(.+)\/(.+)\/(relationships)\/(\w+)(\?.+)?$/';

        if (!preg_match($regex, $object->path, $matches)) {
            return false;
        }

        $object->parentPath = $matches[1];
        $object->parentController = Router::parse($object->parentPath)['controller'];
        $object->parentId = $matches[2];

        $relationship = $matches[4];

        // belongsTo relationship
        if (Inflector::singularize($relationship) === $relationship) {
            $object->relationship = 'belongsTo';

            $object->relationshipController = Inflector::classify($relationship);
            $object->relationshipController = Inflector::pluralize($object->relationshipController);

            $object->relationshipForeignKeyField = $relationship . '_id';

            return $object;
        }

        // hasMany relationship
        $object->relationship = 'hasMany';
        $object->relationshipController = Inflector::camelize($relationship);

        $object->relationshipSearchField = Inflector::tableize($object->parentController);
        $object->relationshipSearchField = Inflector::singularize($object->relationshipSearchField) . '_id';

        return $object;
    }
}
