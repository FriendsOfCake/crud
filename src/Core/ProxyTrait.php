<?php
namespace Crud\Core;

use Cake\Event\Event;
use Crud\Event\Subject;

trait ProxyTrait {

	protected $_entity;

/**
 * Proxy method for `$this->_crud()->action()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return \Crud\Action\Base
 */
	protected function _action($name = null) {
		return $this->_crud()->action($name);
	}

/**
 * Proxy method for `$this->_crud()->trigger()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $eventName
 * @param array $data
 * @return \Cake\Event\Event
 */
	protected function _trigger($eventName, $data = []) {
		return $this->_crud()->trigger($eventName, $data);
	}

/**
 * Proxy method for `$this->_crud()->listener()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return \Crud\Listener\Base
 */
	protected function _listener($name) {
		return $this->_crud()->listener($name);
	}

/**
 * Proxy method for `$this->_crud()->Session`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return \Cake\Controller\Component\SessionComponent
 */
	protected function _session() {
		return $this->_crud()->Session;
	}

/**
 * Proxy method for `$this->_container->_controller`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return \Cake\Controller\Controller
 */
	protected function _controller() {
		return $this->_controller;
	}

/**
 * Proxy method for `$this->_container->_request`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return \Cake\Network\Request
 */
	protected function _request() {
		return $this->_controller()->request;
	}

/**
 * Proxy method for `$this->_controller()->response`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return \Cake\Network\Response
 */
	protected function _response() {
		return $this->_controller()->response;
	}

/**
 * Get a table instance
 *
 * @return \Cake\ORM\Table
 */
	protected function _table() {
		$controller = $this->_controller();
		list(, $modelClass) = pluginSplit($controller->modelClass);
		return $controller->{$modelClass};
	}

/**
 * Get a fresh entity instance from the primary Table
 *
 * @param  array $data
 * @return \Cake\ORM\Entity
 */
	protected function _entity(array $data = []) {
		if ($this->_entity && empty($data)) {
			return $this->_entity;
		}

		return $this->_table()->newEntity($data);
	}

/**
 * Proxy method for `$this->_crud()->getSubject()`
 *
 * @param array $additional
 * @return \Crud\Event\Subject
 */
	protected function _subject($additional = []) {
		return new Subject($additional);
	}

/**
 * Proxy method for `$this->_crud()->validationErrors()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return array
 */
	protected function _validationErrors() {
		return $this->_crud()->validationErrors();
	}

/**
 * Proxy method for `$this->_container->_crud`
 *
 * @return Crud\Controller\Component\CrudComponent
 */
	protected function _crud() {
		if (!$this->_controller->Crud) {
			return $this->_controller->components()->load('Crud.Crud');
		}

		return $this->_controller->Crud;
	}

}
