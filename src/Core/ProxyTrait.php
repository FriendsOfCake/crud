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
use Cake\ORM\Table;
use Crud\Action\BaseAction;
use Crud\Controller\Component\CrudComponent;
use Crud\Event\Subject;
use Crud\Listener\BaseListener;

trait ProxyTrait
{
    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected ?EntityInterface $_entity = null;

    /**
     * Proxy method for `$this->_crud()->action()`
     *
     * Primarily here to ease unit testing
     *
     * @param string|null $name Action name
     * @return \Crud\Action\BaseAction
     * @codeCoverageIgnore
     */
    protected function _action(?string $name = null): BaseAction
    {
        return $this->_crud()->action($name);
    }

    /**
     * Proxy method for `$this->_crud()->trigger()`
     *
     * Primarily here to ease unit testing
     *
     * @param string $eventName Event name
     * @param \Crud\Event\Subject|null $data Event data
     * @return \Cake\Event\EventInterface<\Crud\Event\Subject>
     * @throws \Exception
     * @codeCoverageIgnore
     */
    protected function _trigger(string $eventName, ?Subject $data = null): EventInterface
    {
        return $this->_crud()->trigger($eventName, $data);
    }

    /**
     * Proxy method for `$this->_crud()->listener()`
     *
     * Primarily here to ease unit testing
     *
     * @param string $name Listener name
     * @return \Crud\Listener\BaseListener
     * @throws \Crud\Error\Exception\ListenerNotConfiguredException
     * @throws \Crud\Error\Exception\MissingListenerException
     * @codeCoverageIgnore
     */
    protected function _listener(string $name): BaseListener
    {
        return $this->_crud()->listener($name);
    }

    /**
     * Proxy method to get session instance.
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\Session
     * @codeCoverageIgnore
     */
    protected function _session(): Session
    {
        return $this->_request()->getSession();
    }

    /**
     * Proxy method for `$this->_container->_controller`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Controller\Controller
     * @codeCoverageIgnore
     */
    protected function _controller(): Controller
    {
        return $this->_controller;
    }

    /**
     * Proxy method for `$this->_container->_request`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\ServerRequest
     * @codeCoverageIgnore
     */
    protected function _request(): ServerRequest
    {
        return $this->_controller()->getRequest();
    }

    /**
     * Proxy method for `$this->_controller()->getResponse()`
     *
     * Primarily here to ease unit testing
     *
     * @return \Cake\Http\Response
     * @codeCoverageIgnore
     */
    protected function _response(): Response
    {
        return $this->_controller()->getResponse();
    }

    /**
     * Get the model instance
     *
     * @return \Cake\Datasource\RepositoryInterface
     * @psalm-suppress MoreSpecificReturnType
     */
    protected function _model(): RepositoryInterface
    {
        return $this->_crud()->model();
    }

    /**
     * Get a fresh entity instance from the primary Table
     *
     * @param array $data Data array
     * @param array $options A list of options for the object hydration.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function _entity(array $data = [], array $options = []): EntityInterface
    {
        if ($this->_entity && empty($data)) {
            return $this->_entity;
        }

        return $this->_model()->newEntity($data, $options);
    }

    /**
     * Proxy method for `$this->_crud()->getSubject()`
     *
     * @param array $additional Array of subject properties to set
     * @return \Crud\Event\Subject
     */
    protected function _subject(array $additional = []): Subject
    {
        return new Subject($additional);
    }

    /**
     * Proxy method for `$this->_controller->Crud`
     *
     * @return \Crud\Controller\Component\CrudComponent
     */
    protected function _crud(): CrudComponent
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        return $this->_controller->Crud;
    }
}
