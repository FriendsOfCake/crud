<?php
namespace Crud\Schema\JsonApi;

use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Association;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\View\View;
use Crud\Traits\JsonApiTrait;
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

    use JsonApiTrait;

    /**
     * NeoMerx required property specifying which field to retrieve id from.
     *
     * @var string
     */
    public $idField = 'id';

    /**
     * Holds the instance of Cake\View\View
     * @var \Cake\View\View
     */
    protected $_view;
    /**
     * @var RepositoryInterface
     */
    protected $_repository;

    /**
     * Class constructor
     *
     * @param \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface $factory ContainerInterface
     * @param \Cake\View\View $view Instance of the cake view we are rendering this in
     * @param RepositoryInterface $repository Repository to use
     */
    public function __construct(
        SchemaFactoryInterface $factory,
        View $view,
        RepositoryInterface $repository
    ) {
        $this->_view = $view;

        // NeoMerx required property holding lowercase singular or plural resource name
        if (!isset($this->resourceType)) {
            list (, $entityName) = pluginSplit($repository->registryAlias());

            $this->resourceType = Inflector::underscore($entityName);
        }

        parent::__construct($factory);
        $this->_repository = $repository;
    }

    /**
     * Get resource id.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string
     */
    public function getId($entity)
    {
        return (string)$entity->get($this->_repository->primaryKey());
    }

    /**
     * NeoMerx override used to pass entity root properties to be shown
     * as JsonApi `attributes`.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
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
        foreach ($this->_view->viewVars['_associations'] as $association) {
            $associationKey = Inflector::tableize($association->property());

            $type = $association->type();
            if ($type === Association::MANY_TO_ONE || $type === Association::ONE_TO_ONE) {
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
     * @param \Cake\Datasource\EntityInterface $resource Entity object
     * @param bool $isPrimary True to add resource to data section instead of included
     * @param array $includeRelationships Used to fine tune relationships
     * @return array
     */
    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        $relations = [];

        foreach ($this->_view->viewVars['_associations'] as $association) {
            $associationKey = $association->property();

            $data = $resource->get($associationKey);
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
     * NeoMerx override used to generate `self` links
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string
     */
    public function getSelfSubUrl($entity = null)
    {
        return Router::url($this->_getRepositoryRoutingParameters($this->_repository) + [
            '_method' => 'GET',
            'action' => 'view',
            $entity->get($this->_repository->primaryKey()),
        ], $this->_view->viewVars['_absoluteLinks']);
    }

    /**
     * NeoMerx override to generate belongsTo and hasMany links
     * inside `relationships` node.
     *
     * belongsTo example: /cultures?country_id=1
     * hasMany example"   /countries/1/relationships/currency"
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param string $name Relationship name in lowercase singular or plural
     * @param array $meta Optional array with meta information
     * @param bool $treatAsHref True to NOT prefix url
     *
     * @return \Neomerx\JsonApi\Document\Link
     */
    public function getRelationshipSelfLink($entity, $name, $meta = null, $treatAsHref = false)
    {
        $byProperty = $this->_repository->associations()->getByProperty($name);
        $relatedRepository = $byProperty->target();

        // generate link for belongsTo relationship
        if ($this->_stringIsSingular($name)) {
            if ($this->_view->viewVars['_jsonApiBelongsToLinks'] === true) {
                list(, $controllerName) = pluginSplit($this->_repository->registryAlias());
                $sourceName = Inflector::underscore(Inflector::singularize($controllerName));

                $url = Router::url($this->_getRepositoryRoutingParameters($relatedRepository) + [
                    '_method' => 'GET',
                    'action' => 'view',
                    $sourceName . '_id' => $entity->id,
                    'from' => $this->_repository->registryAlias(),
                    'type' => $name,
                ], $this->_view->viewVars['_absoluteLinks']);
            } else {
                $relatedEntity = $entity[$name];

                $url = Router::url($this->_getRepositoryRoutingParameters($relatedRepository) + [
                    '_method' => 'GET',
                    'action' => 'view',
                    $relatedEntity->get($relatedRepository->primaryKey()),
                ], $this->_view->viewVars['_absoluteLinks']);
            }

            return new Link($url, $meta, $treatAsHref);
        }

        $searchKey = Inflector::tableize($this->_getClassName($entity));
        $searchKey = Inflector::singularize($searchKey) . '_id';

        $url = Router::url($this->_getRepositoryRoutingParameters($relatedRepository) + [
            '_method' => 'GET',
            $searchKey => $entity->id,
        ], $this->_view->viewVars['_absoluteLinks']);

        return new Link($url, $meta, $treatAsHref);
    }

    /**
     * NeoMerx override used to generate `self` links inside `included` node.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return array
     */
    public function getIncludedResourceLinks($entity)
    {
        list(, $entityName) = namespaceSplit(get_class($entity));

        $byProperty = $this->_repository->associations()->getByProperty(Inflector::underscore($entityName));
        if (!$byProperty) {
            return [];
        }
        $repository = $byProperty->target();

        $url = Router::url($this->_getRepositoryRoutingParameters($repository) + [
            '_method' => 'GET',
            'action' => 'view',
            $entity->get($repository->primaryKey()),
        ], $this->_view->viewVars['_absoluteLinks']);

        $links = [
            LinkInterface::SELF => new Link($url),
        ];

        return $links;
    }
}
