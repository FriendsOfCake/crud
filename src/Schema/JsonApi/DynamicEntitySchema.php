<?php
namespace Crud\Schema\JsonApi;

use Cake\Utility\Inflector;
use Cake\View\View;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Schema\SchemaProvider;

class DynamicEntitySchema extends SchemaProvider
{
    /**
     * The default field used for an id
     * @var string
     */
    public $idField = 'id';

    /**
     * Holds the instance of Cake\View\View
     * @var Cake\View\View
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

        if (!$this->resourceType) {
            $this->resourceType = strtolower(Inflector::pluralize($entityName));
        }

        parent::__construct($factory);
    }

    /**
     * Magic accessor for helpers.
     *
     * @param string $name Name of the attribute to get.
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_view->__get($name);
    }

    /**
     * Get resource id.
     *
     * @param \Cake\ORM\Entity $entity Entity object
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
     * @param \Cake\ORM\Entity $entity Entity object
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
     * @param \Cake\ORM\Entity $entity Entity object
     * @param bool $isPrimary True to add resource to data section instead of included
     * @param array $includeRelationships Used to fine tune relationships
     * @return array
     */
    public function getRelationships($entity, $isPrimary, array $includeRelationships)
    {
        $relations = [];

        foreach ($this->_view->get('_associations') as $association) {
            $associationKey = Inflector::tableize($association->name());

            if (get_class($association) === 'Cake\ORM\Association\BelongsTo') {
                $associationKey = Inflector::singularize($associationKey);
            }

            $relations[$associationKey] = [
                    self::DATA => $entity->$associationKey,
                    self::SHOW_SELF => true,
                    self::SHOW_RELATED => true,
            ];
        }

        return $relations;
    }

    /**
     * Return the view instance
     *
     * @return Cake\View\View View instance
     */
    public function getView()
    {
        return $this->_view;
    }
}
