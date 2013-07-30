<?php
/**
 * Crud Base Class
 *
 * Implement base methods used in CrudAction and CrudListener classes
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
abstract class CrudBaseObject extends Object {

/**
 * Proxy method for `$this->_crud->action()`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return CrudAction
 */
	protected function _action($name = null) {
		return $this->_crud->action($name);
	}

/**
 * Proxy method for `$this->_trigger()`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $eventName
 * @param array $data
 * @return CrudSubject
 */
	protected function _trigger($eventName, $data = array()) {
		return $this->_crud->trigger($eventName, $data);
	}

/**
 * Proxy method for `$this->_crud->listener()`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @param string $name
 * @return CrudListener
 */
	protected function _listener($name) {
		return $this->_crud->listener($name);
	}

/**
 * Proxy method for `$this->_crud->Session`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return SessionComponent
 */
	protected function _session() {
		return $this->_crud->Session;
	}

/**
 * Proxy method for `$this->_controller`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return Controller
 */
	protected function _controller() {
		return $this->_controller;
	}

/**
 * Proxy method for `$this->_request`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return CakeRequest
 */
	protected function _request() {
		return $this->_request;
	}

/**
 * Proxy method for `$this->_model`
 *
 * Primary here to ease unit testing
 *
 * @codeCoverageIgnore
 * @return Model
 */
	protected function _model() {
		return $this->_model;
	}

/**
 * Proxy method for `$this->_crud->getSubject()`
 *
 * @codeCoverageIgnore
 * @param array $additional
 * @return CrudSUbject
 */
	protected function _subject($additional = array()) {
		return $this->_crud->getSubject($additional);
	}

}
