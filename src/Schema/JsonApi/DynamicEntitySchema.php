<?php
namespace Crud\Schema\JsonApi;

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
        foreach ($this->_view->viewVars['_associations'] as $association) {
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

        foreach ($this->_view->viewVars['_associations'] as $association) {
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
     * NeoMerx override used to generate `self` links
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string
     */
    public function getSelfSubUrl($entity = null)
    {
        return $this->_getCakeSubUrl($entity, $this->_view->viewVars['_absoluteLinks']) . '/' . $entity->id;
    }

    /**
     * NeoMerx override to generate belongsTo and hasMany links
     * inside `relationships` node.
     *
     * belongsTo example: /cultures?country_id=1
     * hasMany example"   /countries/1/relationships/currency"
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param string $name Relationship name in lowercase singular or plural
     * @param array $meta Optional array with meta information
     * @param bool $treatAsHref True to NOT prefix url
     *
     * @return \Neomerx\JsonApi\Document\Link
     */
    public function getRelationshipSelfLink($entity, $name, $meta = null, $treatAsHref = false)
    {
        // generate link for belongsTo relationship
        if ($this->_stringIsSingular($name)) {
            if ($this->_view->viewVars['_jsonApiBelongsToLinks'] === true) {
                $url = $this->_getCakeSubUrl($entity, $this->_view->viewVars['_absoluteLinks']) . '/' . $entity->id . '/relationships/' . $name;
            } else {
                $relatedEntity = $entity[$name];
                $url = $this->_getCakeSubUrl($relatedEntity, $this->_view->viewVars['_absoluteLinks']) . '/' . $relatedEntity->id;
            }

            return new Link($url, $meta, $treatAsHref);
        }

        // get the target controller required for generating hasMany link
        $relatedEntity = $entity[$name][0];

        $searchKey = Inflector::tableize($this->_getClassName($entity));
        $searchKey = Inflector::singularize($searchKey) . '_id';

        $url = $this->_getCakeSubUrl($relatedEntity, $this->_view->viewVars['_absoluteLinks']) . '?' . $searchKey . '=' . $entity->id;

        return new Link($url, $meta, $treatAsHref);
    }

    /**
     * NeoMerx override used to generate `self` links inside `included` node.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return array
     */
    public function getIncludedResourceLinks($entity)
    {
        $url = $this->_getCakeSubUrl($entity, $this->_view->viewVars['_absoluteLinks']) . '/' . $entity->id;

        $links = [
            LinkInterface::SELF => new Link($url),
        ];

        return $links;
    }
}
