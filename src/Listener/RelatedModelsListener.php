<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Association;
use Cake\Utility\Inflector;
use RuntimeException;

/**
 * Implements beforeRender event listener to set related models' lists to
 * the view
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RelatedModelsListener extends BaseListener
{
    /**
     * Returns a list of events this listener is interested in.
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Crud.beforePaginate' => 'beforePaginate',
            'Crud.beforeRender' => 'beforeRender',
        ];
    }

    /**
     * Automatically parse and contain related table classes
     *
     * @param \Cake\Event\EventInterface $event Before paginate event
     * @return void
     */
    public function beforePaginate(EventInterface $event): void
    {
        $method = 'contain';
        if (method_exists($event->getSubject()->query, 'getContain')) {
            $method = 'getContain';
        }
        $contained = $event->getSubject()->query->$method();

        if (!empty($contained)) {
            return;
        }

        $models = $this->models();
        if (empty($models)) {
            return;
        }

        $event->getSubject()->query->contain(array_keys($models));
    }

    /**
     * Fetches related models' list and sets them to a variable for the view
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     * @codeCoverageIgnore
     */
    public function beforeRender(EventInterface $event): void
    {
        $entity = null;
        if (isset($event->getSubject()->entity)) {
            $entity = $event->getSubject()->entity;
        }
        $this->publishRelatedModels(null, $entity);
    }

    /**
     * Find and publish all related models to the view
     * for an action
     *
     * @param null|string $action If NULL the current action will be used
     * @param null|\Cake\Datasource\EntityInterface $entity The optional entity for which we we trying to find related
     * @return void
     */
    public function publishRelatedModels(?string $action = null, ?EntityInterface $entity = null): void
    {
        $models = $this->models($action);

        if (empty($models)) {
            return;
        }

        $controller = $this->_controller();

        foreach ($models as $name => $association) {
            [, $associationName] = pluginSplit($association->getName());
            $viewVar = Inflector::variable($associationName);
            if (array_key_exists($viewVar, $controller->viewBuilder()->getVars())) {
                continue;
            }

            $finder = $this->finder($association);
            $query = $association->find($finder, $this->_findOptions($association));
            $subject = $this->_subject(compact('name', 'viewVar', 'query', 'association', 'entity'));
            $event = $this->_trigger('relatedModel', $subject);

            $controller->set($event->getSubject()->viewVar, $event->getSubject()->query->toArray());
        }
    }

    /**
     * Find keyField and valueField for find('list')
     *
     * This is useful for cases where the relation has a different binding key
     * than the primary key in the associated table (e.g. NOT 'id')
     *
     * @param \Cake\ORM\Association $association The association that we process
     * @return array
     */
    protected function _findOptions(Association $association): array
    {
        return [
            'keyField' => $association->getBindingKey(),
        ];
    }

    /**
     * Get finder to use for provided association.
     *
     * @param \Cake\ORM\Association $association Association instance
     * @return string
     */
    public function finder(Association $association): string
    {
        if ($association->getTarget()->behaviors()->has('Tree')) {
            return 'treeList';
        }

        return 'list';
    }

    /**
     * Gets the list of associated model lists to be fetched for an action
     *
     * @param string|null $action name of the action
     * @return array
     */
    public function models(?string $action = null): array
    {
        $settings = $this->relatedModels(null, $action);

        if ($settings === true) {
            return $this->getAssociatedByType(['oneToOne', 'manyToMany', 'manyToOne']);
        }

        if (empty($settings)) {
            return [];
        }

        if (is_string($settings)) {
            $settings = [$settings];
        }

        return $this->getAssociatedByName($settings);
    }

    /**
     * Set or get the related models that should be found
     * for the action
     *
     * @param mixed $related Everything but `null` will change the configuration
     * @param string|null $action The action to configure
     * @return mixed
     */
    public function relatedModels($related = null, ?string $action = null)
    {
        if ($related === null) {
            return $this->_action($action)->getConfig('relatedModels');
        }

        return $this->_action($action)->setConfig('relatedModels', $related, false);
    }

    /**
     * Get associated tables based on the current table instance based on their
     * association type
     *
     * @param array $types Association types
     * @return array
     */
    public function getAssociatedByType(array $types = []): array
    {
        $return = [];

        $table = $this->_table();
        foreach ($table->associations()->keys() as $association) {
            /** @var \Cake\ORM\Association $associationClass */
            $associationClass = $table->associations()->get($association);
            if (!in_array($associationClass->type(), $types)) {
                continue;
            }

            $return[$associationClass->getName()] = $associationClass;
        }

        return $return;
    }

    /**
     * Get associated tables based on the current table instance based on their
     * association name
     *
     * @param array $names Association names
     * @return array
     * @throws \RuntimeException when association not found.
     */
    public function getAssociatedByName(array $names): array
    {
        $return = [];

        $table = $this->_table();
        foreach ($names as $association) {
            $associationClass = $table->associations()->get($association);
            if (!$associationClass) {
                throw new RuntimeException(sprintf(
                    'Table "%s" is not associated with "%s"',
                    get_class($table),
                    $association
                ));
            }
            $return[$associationClass->getName()] = $associationClass;
        }

        return $return;
    }
}
