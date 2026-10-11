<?php
declare(strict_types=1);

namespace Crud\Core;

use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Crud\Action\BaseAction;
use Crud\Controller\Component\CrudComponent;
use Crud\Event\Subject;
use Crud\Listener\BaseListener;

trait ProxyTrait
{
    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected ?EntityInterface $entity = null;

    /**
     * Proxy method for `$this->crud()->action()`
     *
     * Primarily here to ease unit testing
     *
     * @param string|null $name Action name
     * @return \Crud\Action\BaseAction
     * @codeCoverageIgnore
     */
    protected function action(?string $name = null): BaseAction
    {
        return $this->crud()->action($name);
    }

    /**
     * Proxy method for `$this->crud()->trigger()`
     *
     * Primarily here to ease unit testing
     *
     * @param string $eventName Event name
     * @param \Crud\Event\Subject|null $data Event data
     * @return \Cake\Event\EventInterface<\Crud\Event\Subject>
     * @throws \Exception
     * @codeCoverageIgnore
     */
    protected function trigger(string $eventName, ?Subject $data = null): EventInterface
    {
        return $this->crud()->trigger($eventName, $data);
    }

    /**
     * Proxy method for `$this->crud()->listener()`
     *
     * Primarily here to ease unit testing
     *
     * @param string $name Listener name
     * @return \Crud\Listener\BaseListener
     * @throws \Crud\Error\Exception\ListenerNotConfiguredException
     * @throws \Crud\Error\Exception\MissingListenerException
     * @codeCoverageIgnore
     */
    protected function listener(string $name): BaseListener
    {
        return $this->crud()->listener($name);
    }

    /**
     * Proxy method to get session instance.
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\Session
     * @codeCoverageIgnore
     */
    protected function session(): Session
    {
        return $this->request()->getSession();
    }

    /**
     * Proxy method for `$this->_container->controller`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Controller\Controller
     * @codeCoverageIgnore
     */
    protected function controller(): Controller
    {
        return $this->controller;
    }

    /**
     * Proxy method for `$this->_container->request`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\ServerRequest
     * @codeCoverageIgnore
     */
    protected function request(): ServerRequest
    {
        return $this->controller()->getRequest();
    }

    /**
     * Proxy method for `$this->controller()->getResponse()`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\Response
     * @codeCoverageIgnore
     */
    protected function response(): Response
    {
        return $this->controller()->getResponse();
    }

    /**
     * Get the model instance
     *
     * @return \Cake\Datasource\RepositoryInterface
     */
    protected function model(): RepositoryInterface
    {
        return $this->crud()->model();
    }

    /**
     * Get a fresh entity instance from the primary Table
     *
     * @param array $data Data array
     * @param array $options A list of options for the object hydration.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function entity(array $data = [], array $options = []): EntityInterface
    {
        if ($this->entity && $data === []) {
            return $this->entity;
        }

        return $this->model()->newEntity($data, $options);
    }

    /**
     * Proxy method for `$this->crud()->getSubject()`
     *
     * @param array $additional Array of subject properties to set
     * @return \Crud\Event\Subject
     */
    protected function subject(array $additional = []): Subject
    {
        return new Subject($additional);
    }

    /**
     * Proxy method for `$this->controller->Crud`
     *
     * @return \Crud\Controller\Component\CrudComponent
     */
    protected function crud(): CrudComponent
    {
        return $this->controller->Crud;
    }
}
