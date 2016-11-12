<?php
namespace Crud\Schema\JsonApi;

use Cake\Utility\Inflector;
use Cake\View\View;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\SchemaProvider;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DynamicEntitySchema extends SchemaProvider
{
    /**
     * NeoMerx required property specifying which field to retrieve id from.
     *
     * @var string
     */
    public $idField = 'id';

    /**
     * Class constructor
     *
     * @param \Neomerx\JsonApi\Contracts\Schema\ContainerInterface $factory ContainerInterface
     * @param \Cake\View\View $view Instance of the cake view we are rendering this in
     * @param string $entityName Name of the entity this schema is for
     */
    public function __construct(
        SchemaFactoryInterface $factory,
        View $view,
        $entityName
    ) {
        $this->_view = $view;

        // NeoMerx required property holding lowercase singular or plural resource name
        if (!isset($this->resourceType)) {
            $this->resourceType = strtolower(Inflector::pluralize($entityName));
        }

        parent::__construct($factory);
    }

    /**
     * Get resource id.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string
     */
    public function getId($entity)
    {
        return (string)$entity->get($this->idField);
    }

    /**
     * NeoMerx override used to pass entity root properties to be shown
     * as JsonApi `attributes`.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return array
     */
    public function getAttributes($entity)
    {
        if ($entity->has($this->idField)) {
            $hidden = array_merge($entity->hiddenProperties(), [$this->idField]);
            $entity->hiddenProperties($hidden);
        }

        $attributes = $entity->toArray();

        // remove associated data so it won't appear inside jsonapi `attributes`
        foreach ($this->_view->get('_associations') as $association) {
            $associationKey = Inflector::tableize($association->table());

            if (get_class($association) === 'Cake\ORM\Association\BelongsTo') {
                $associationKey = Inflector::singularize($associationKey);
            }

            unset($attributes[$associationKey]);
        }

        return $attributes;
    }

    /**
     * NeoMerx override used to pass associated entity names to be used for
     * generating JsonApi `relationships`.
     *
     * JSON API optional `related` links not implemented yet.
     *
     * @param \Cake\ORM\Entity $resource Entity object
     * @param bool $isPrimary True to add resource to data section instead of included
     * @param array $includeRelationships Used to fine tune relationships
     * @return array
     */
    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        $relations = [];

        foreach ($this->_view->get('_associations') as $association) {
            $associationKey = Inflector::tableize($association->name());

            if (get_class($association) === 'Cake\ORM\Association\BelongsTo') {
                $associationKey = Inflector::singularize($associationKey);
            }

            $data = $resource->$associationKey;
            if (!$data) {
                continue;
            }

            $relations[$associationKey] = [
                self::DATA => $data,
                self::SHOW_SELF => true,
                self::SHOW_RELATED => false,
            ];
        }

        return $relations;
    }

    /**
     * NeoMerx override to generate Cake belongsTo and hasMany links.
     *
     * @param \Cake\ORM\Entity $resource Entity
     * @param string $name Relationship name in lowercase singular or plural
     * @param array $meta Optional array with meta information
     * @param bool $treatAsHref True to NOT prefix url
     *
     * @return \Neomerx\JsonApi\Document\Link
     */
    public function getRelationshipSelfLink($resource, $name, $meta = null, $treatAsHref = false)
    {
        $entityKey = $this->_getUrlController($resource);// E.g. currencies or cultures

        // belongsTo relationship
        if ($this->_isSingular($name)) {
            $url = '/' . $entityKey . '/' . $this->getId($resource) . '/relationships/' . $name;

            return new Link($url, $meta, $treatAsHref);
        }

        // hasMany relationship
        $targetController = $name;
        $searchKey = Inflector::tableize($this->_getClassName($resource));
        $searchKey = Inflector::singularize($searchKey) . '_id';

        $url = '/' . $targetController . '?' . $searchKey . '=' . $resource->id;

        return new Link($url, $meta, $treatAsHref);
    }

    /**
     * NeoMerx override used to generate `self` links inside `included` node.
     *
     * @param \Cake\ORM\Entity $resource Entity
     * @return array
     */
    public function getIncludedResourceLinks($resource)
    {
        $controller = $this->_getUrlController($resource);

        $links = [
            LinkInterface::SELF => new Link('/' . $controller . '/' . $resource->id),
        ];

        return $links;
    }

    /**
     * Parses the name of an Entity class to build a lowercase plural
     * controller name to be used in links.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string Lowercase controller name
     */
    protected function _getUrlController($entity)
    {
        $controller = $this->_getClassName($entity);

        if ($this->_isSingular($controller)) {
            $controller = Inflector::pluralize($controller);
        }

        return Inflector::tableize($controller);
    }

    /**
     * Helper function to return the class name of an object without namespace.
     *
     * @param mixed $class Any php class object
     * @return bool|string False if the classname could not be derived
     */
    protected function _getClassName($class)
    {
        $className = get_class($class);

        if ($pos = strrpos($className, '\\')) {
            return substr($className, $pos + 1);
        }

        return false;
    }

    /**
     * Helper function to determine if string is singular or plural.
     *
     * @param string $string Preferably a CakePHP generated name.
     * @return bool
     */
    protected function _isSingular($string)
    {
        if (Inflector::singularize($string) === $string) {
            return true;
        }

        return false;
    }
}
