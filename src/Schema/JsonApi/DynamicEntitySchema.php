<?php
namespace Crud\Schema\JsonApi;

use Cake\Datasource\EntityInterface;
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
            $method = isset($view->viewVars['_inflect']) ? $view->viewVars['_inflect'] : 'dasherize';
            $this->resourceType = Inflector::$method($entityName);
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
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return \Cake\Datasource\RepositoryInterface $repository
     */
    protected function _getRepository($entity)
    {
        $repositoryName = $entity->source();

        return isset($this->_view->viewVars['_repositories'][$repositoryName]) ? $this->_view->viewVars['_repositories'][$repositoryName] : null;
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
        foreach ($this->_repository->associations() as $association) {
            $propertyName = $association->property();

            if ($association->type() === Association::MANY_TO_ONE) {
                $foreignKey = $association->foreignKey();
                unset($attributes[$foreignKey]);
            }

            unset($attributes[$propertyName]);
        }

        return $attributes;
    }

    /**
     * NeoMerx override used to pass associated entity names to be used for
     * generating JsonApi `relationships`.
     *
     * JSON API optional `related` links not implemented yet.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param bool $isPrimary True to add resource to data section instead of included
     * @param array $includeRelationships Used to fine tune relationships
     * @return array
     */
    public function getRelationships($entity, $isPrimary, array $includeRelationships)
    {
        $relations = [];

        foreach ($this->_repository->associations() as $association) {
            $property = $association->property();

            $data = $entity->get($property);
            if (!$data) {
                continue;
            }

            // change related  data in entity to dasherized if need be
            if ($this->_view->viewVars['_inflect'] === 'dasherize') {
                $dasherizedProperty = Inflector::dasherize($property);

                if (empty($entity->$dasherizedProperty)) {
                    $entity->$dasherizedProperty = $entity->$property;
                    unset($entity->$property);
                    $property = $dasherizedProperty;
                }
            }

            $relations[$property] = [
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
        if ($this->_view->viewVars['_inflect'] === 'dasherize') {
            $name = Inflector::underscore($name);
        }

        $association = $this->_repository->associations()->getByProperty($name);
        $relatedRepository = $association->target();

        // generate link for belongsTo relationship
        if (in_array($association->type(), [Association::MANY_TO_ONE, Association::ONE_TO_ONE])) {
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
                $name = Inflector::dasherize($name);
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
            'action' => 'index',
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
        $repositoryName = $entity->source();
        if (!isset($this->_view->viewVars['_repositories'][$repositoryName])) {
            return [];
        }
        $repository = $this->_view->viewVars['_repositories'][$repositoryName];

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
