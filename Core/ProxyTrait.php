<?php

namespace Crud\Core;

use Cake\Event\Event;

trait ProxyTrait {

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
		return $this->_crud()->controller();
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
 * Proxy method for `$this->_container->_model`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return Model
 */
	protected function _model() {
		return $this->_crud()->model;
	}

	protected function _repository() {
		return $this->_crud()->repository();
	}

	protected function _entity(array $data = []) {
		return $this->_crud()->entity($data);
	}

/**
 * Proxy method for `$this->_crud()->getSubject()`
 *
 * @codeCoverageIgnore
 * @param array $additional
 * @return \Crud\Event\Subject
 */
	protected function _subject($additional = []) {
		return $this->_crud()->getSubject($additional);
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
		return $this->_Crud;
	}

}
