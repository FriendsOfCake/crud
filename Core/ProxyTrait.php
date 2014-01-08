<?php

namespace Crud\Core;

trait ProxyTrait {

/**
 * Proxy method for `$this->_crud()->action()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return CrudAction
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
 * @return CrudSubject
 */
	protected function _trigger($eventName, $data = array()) {
		return $this->_crud()->trigger($eventName, $data);
	}

/**
 * Proxy method for `$this->_crud()->listener()`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return CrudListener
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
 * @return SessionComponent
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
 * @return Controller
 */
	protected function _controller() {
		return $this->_container->controller;
	}

/**
 * Proxy method for `$this->_container->_request`
 *
 * Primarily here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return CakeRequest
 */
	protected function _request() {
		return $this->_container->request;
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
		return $this->_container->model;
	}

	protected function _repository() {
		return $this->_crud()->repository();
	}

	protected function _entity() {
		return $this->_crud()->entity();
	}

/**
 * Proxy method for `$this->_crud()->getSubject()`
 *
 * @codeCoverageIgnore
 * @param array $additional
 * @return CrudSUbject
 */
	protected function _subject($additional = array()) {
		return $this->_crud()->getSubject($additional);
	}

/**
 * Proxy method for `$this->_container->_crud`
 *
 * @return CrudComponent
 */
	protected function _crud() {
		return $this->_container->crud;
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

}
