<?php
namespace Crud\Routing\Route;

use Cake\ORM\TableRegistry;
use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiRoute extends Route
{
    /**
     * Parse method.
     *
     * @param string $url The JSON API URL to attempt to parse
     * @param string $method The HTTP method of the request being parsed
     * @return mixed URL parameter array on success, false otherwise
     */
    public function parse($url, $method = '')
    {
        $params = $this->_isRelationshipSelfLink($url);

        if ($params === false) {
            return false;
        }

        return $params;
    }

    /**
     * Detects and handles JSON API relationship `self` links.
     *
     * Please note that by design JSON API relationship `self` links by do NOT
     * contain a foreign key/id since they are intended to always point to the
     * actual related record. This is why we first need to lookup the main
     * resource record, then extract the actual/current foreign key and finally
     * return the `$params` array pointing to the related controller.
     *
     * Example URL: http://my.api.local/countries/3/relationships/currency
     *
     * In the above example we would first lookup country with `id` 3, then
     * extract from that result the foreign key `currency_id` before returning
     * a params array pointing to CurrenciesController, `view` action and e.g.
     * `id` 2 (if `currency_id` found in the main record was 2).
     *
     * @param string $url URL to parse
     * @return mixed URL parameter array on success, false otherwise
     */
    protected function _isRelationshipSelfLink($url)
    {
        if (!preg_match('/^\/(\w+)\/(.+)\/relationships\/(\w+)/', $url, $matches)) {
            return false;
        }

        $mainResourceName = $matches[1]; // e.g. countries
        $mainResourceId = $matches[2]; // e.g. 2 (for country with id 2)
        $relationshipKey = $matches[3]; // e.g. currency or languages (belongsTo, hasMany)

        // fetch main resource from database
        $mainResourceTable = TableRegistry::get($mainResourceName);
        $result = $mainResourceTable
            ->find()
            ->where([
                'id' => $mainResourceId
            ])
            ->first();

        if (empty($result)) {
            return false;
        }

        // try belongsTo first
        if ($result[$relationshipKey . '_id']) {
            $controller = Inflector::classify($relationshipKey);
            $controller = Inflector::pluralize($controller);

            return [
                'controller' => $controller,
                'action' => 'view',
                'pass' => [
                    $result[$relationshipKey . '_id']
                ]
            ];
        }

        // no idea on how to handle hasMany redirect yet so returning false
        // (perhaps do'able with search plugin and query parameter).
        return false;
    }
}
