<?php
namespace Crud\Listener;

use Cake\Event\Event;
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
     * Fetches related models' list and sets them to a variable for the view
     *
     * @param Event $event Event
     * @return void
     * @codeCoverageIgnore
     */
    public function beforeRender(Event $event)
    {
        $this->publishRelatedModels();
    }

    /**
     * Find and publish all related models to the view
     * for an action
     *
     * @param NULL|string $action If NULL the current action will be used
     * @return void
     */
    public function publishRelatedModels($action = null)
    {
        $models = $this->models($action);

        if (empty($models)) {
            return;
        }

        $controller = $this->_controller();

        foreach ($models as $name => $association) {
            list(, $associationName) = pluginSplit($association->name());
            $viewVar = Inflector::variable($associationName);
            if (array_key_exists($viewVar, $controller->viewVars)) {
                continue;
            }

            $query = $association->find('list');
            $subject = $this->_subject(compact('name', 'viewVar', 'query', 'association'));
            $event = $this->_trigger('relatedModel', $subject);

            $controller->set($event->subject->viewVar, $event->subject->query->toArray());
        }
    }

    /**
     * Gets the list of associated model lists to be fetched for an action
     *
     * @param string $action name of the action
     * @return array
     */
    public function models($action = null)
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
     * @param string $action The action to configure
     * @return mixed
     */
    public function relatedModels($related = null, $action = null)
    {
        if ($related === null) {
            return $this->_action($action)->config('relatedModels');
        }

        return $this->_action($action)->config('relatedModels', $related, false);
    }

    /**
     * Get associated tables based on the current table instance based on their
     * association type
     *
     * @param array $types Association types
     * @return array
     */
    public function getAssociatedByType($types = [])
    {
        $return = [];

        $table = $this->_table();
        foreach ($table->associations()->keys() as $association) {
            $associationClass = $table->associations()->get($association);
            if (!in_array($associationClass->type(), $types)) {
                continue;
            }

            $return[$associationClass->name()] = $associationClass;
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
    public function getAssociatedByName($names)
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
            $return[$associationClass->name()] = $associationClass;
        }

        return $return;
    }
}
