<?php

App::uses('CakeEventListener', 'Event');
App::uses('Hash', 'Utility');

/**
 * The Base Crud Listener
 *
 * All callbacks are defined here for good measure
 *
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
abstract class CrudListener extends Object implements CakeEventListener {

/**
 * Reference to the CakeRequest
 *
 * @var CakeRequest
 */
	protected $_request;

/**
 * Reference to the Controller
 *
 * @var Controller
 */
	protected $_controller;

/**
 * Crud Component reference
 *
 * @var CrudComponent
 */
	protected $_crud;

/**
 * Crud Event subject
 *
 * @var CrudSubject
 */
	protected $_subject;

/**
 * Listener configuration
 *
 * @var array
 */
	protected $_settings = array();

/**
 * Class constructor
 *
 * @param string $prefix CRUD component events name prefix
 * @param array $models List of models to be fetched in beforeRenderEvent
 * @return void
 */
	public function __construct(CrudSubject $subject, $defaults = null) {
		$this->_crud = $subject->crud;
		$this->_subject = $subject;
		$this->_request = $subject->request;
		$this->_controller = $subject->controller;

		if (!empty($defaults)) {
			$this->config($defaults);
		}
	}

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * - init : Called before any other method in the decorator.
 *     Just set the arguments as instance properties for easier access later
 * - recordNotFound : Called if a find() did not return any records
 * - beforePaginate : Called right before any paginate() method
 * - afterPaginate : Called right after any paginate() method
 * - invalidId : Called if the ID format validation failed
 * - setFlash : Called before any CakeSession::setFlash
 *     Subject contains the following keys you can modify:
 * 	     - message
 * 	     - element = 'default',
 * 	     - params = array()
 * 	     - key = 'flash'
 *
 * @codeCoverageIgnore
 * @return array
 */
	public function implementedEvents() {
		$eventMap = array(
			'Crud.init'	=> 'init',

			'Crud.beforePaginate' => 'beforePaginate',
			'Crud.afterPaginate' => 'afterPaginate',

			'Crud.recordNotFound' => 'recordNotFound',
			'Crud.invalidId' => 'invalidId',
			'Crud.setFlash' => 'setFlash',

			'Crud.beforeRender' => 'beforeRender',
			'Crud.beforeRedirect' => 'beforeRedirect',

			'Crud.beforeSave' => 'beforeSave',
			'Crud.afterSave' => 'afterSave',

			'Crud.beforeFind' => 'beforeFind',
			'Crud.afterFind' => 'afterFind',

			'Crud.beforeDelete' => 'beforeDelete',
			'Crud.afterDelete' => 'afterDelete'
		);

		$events = array();
		foreach ($eventMap as $event => $method) {
			if (method_exists($this, $method)) {
				$event[$event] = $method;
			}
		}

		return $events;
	}

/**
 * Sets a configuration variable into this listener
 *
 * If called with no arguments, all configuration values are
 * returned.
 *
 * $key is interpreted with dot notation, like the one used for
 * Configure::write()
 *
 * If $key is string and $value is not passed, it will return the
 * value associated with such key.
 *
 * If $key is an array and $value is empty, then $key will
 * be interpreted as key => value dictionary of settings and
 * it will be merged directly with $this->settings
 *
 * If $key is a string, the value will be inserted in the specified
 * slot as indicated using the dot notation
 *
 * @param mixed $key
 * @param mixed $value
 * @return mixed|CrudAction
 */
	public function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return $this->_settings;
		}

		if (is_null($value)) {
			if (is_array($key)) {
				$this->_settings = $key + $this->_settings;
				return $this;
			}

			return Hash::get($this->_settings, $key);
		}

		if (is_array($value)) {
			$value = $value + (array)Hash::get($this->_settings, $key);
		}

		$this->_settings = Hash::insert($this->_settings, $key, $value);
		return $this;
	}

}
