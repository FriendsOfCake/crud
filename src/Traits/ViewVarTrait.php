<?php
declare(strict_types=1);

namespace Crud\Traits;

use Cake\Event\EventInterface;
use Cake\Utility\Inflector;
use Exception;

trait ViewVarTrait
{
    /**
     * Publish the viewVar so people can do $$viewVar and end up
     * wit the entity in the view
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return void
     * @throws \Exception
     */
    public function publishViewVar(EventInterface $event): void
    {
        if (!$this->responding()) {
            return;
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
     * @throws \Exception
     */
    public function viewVar(mixed $name = null): mixed
    {
        if (empty($name)) {
            return $this->getConfig('viewVar') ?: $this->_deriveViewVar();
        }

        return $this->setConfig('viewVar', $name);
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
    protected function _deriveViewVar(): string
    {
        if ($this->scope() === 'table') {
            return Inflector::variable($this->_controller()->getName());
        }

        if ($this->scope() === 'entity') {
            return Inflector::variable(Inflector::singularize($this->_controller()->getName()));
        }

        throw new Exception('Unknown action scope: ' . (string)$this->scope());
    }

    /**
     * Derive the viewVar value based on the scope of the action
     * as well as the Event being handled
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return mixed
     * @throws \Exception
     */
    protected function _deriveViewValue(EventInterface $event): mixed
    {
        $key = $this->_action()->subjectEntityKey();

        return $event->getSubject()->{$key};
    }
}
