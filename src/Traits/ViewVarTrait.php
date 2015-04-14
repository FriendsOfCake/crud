<?php
namespace Crud\Traits;

use Cake\Utility\Inflector;

trait ViewVarTrait
{

    /**
     * Publish the viewVar so people can do $$viewVar and end up
     * wit the entity in the view
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function publishViewVar(\Cake\Event\Event $event)
    {
        if (!$this->responding()) {
            return false;
        }

        $viewVar = $this->viewVar();
        $controller = $this->_controller();

        $controller->set($viewVar, $this->_deriveViewValue($event));
        $controller->set('viewVar', $viewVar);
    }

    /**
     * Change the name of the view variable name
     * of the data when its sent to the view
     *
     * @param mixed $name Var name
     * @return mixed
     */
    public function viewVar($name = null)
    {
        if (empty($name)) {
            return $this->config('viewVar') ?: $this->_deriveViewVar();
        }

        return $this->config('viewVar', $name);
    }

    /**
     * Derive the viewVar based on the scope of the action
     *
     * Actions working on a single entity will use singular name,
     * and actions working on a full table will use plural name
     *
     * @throws \Exception
     * @return string
     */
    protected function _deriveViewVar()
    {
        if ($this->scope() === 'table') {
            return Inflector::variable($this->_controller()->name);
        }

        if ($this->scope() === 'entity') {
            return Inflector::variable(Inflector::singularize($this->_controller()->name));
        }

        throw new \Exception('Unknown action scope: ' . $this->scope());
    }

    /**
     * Derive the viewVar value based on the scope of the action
     * as well as the Event being handled
     *
     * @param \Cake\Event\Event $event Event
     * @return mixed
     * @throws \Exception
     */
    protected function _deriveViewValue(\Cake\Event\Event $event)
    {
        $key = $this->_action()->subjectEntityKey();
        return $event->subject->{$key};
    }
}
