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

		if (!empty($defaults)) {
			$this->config($defaults);
		}
	}

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.init'	=> array('callable' => 'init'),

			'Crud.beforePaginate' => array('callable' => 'beforePaginate'),
			'Crud.afterPaginate' => array('callable' => 'afterPaginate'),

			'Crud.recordNotFound' => array('callable' => 'recordNotFound'),
			'Crud.invalidId' => array('callable' => 'invalidId'),
			'Crud.setFlash' => array('callable' => 'setFlash'),

			'Crud.beforeRender' => array('callable' => 'beforeRender'),
			'Crud.beforeRedirect' => array('callable' => 'beforeRedirect'),

			'Crud.beforeSave' => array('callable' => 'beforeSave'),
			'Crud.afterSave' => array('callable' => 'afterSave'),

			'Crud.beforeFind' => array('callable' => 'beforeFind'),
			'Crud.afterFind' => array('callable' => 'afterFind'),

			'Crud.beforeDelete' => array('callable' => 'beforeDelete'),
			'Crud.afterDelete' => array('callable' => 'afterDelete'),

			'Crud.beforeListRelated' => array('callable' => 'beforeListRelated'),
			'Crud.afterListRelated'	=> array('callable' => 'afterListRelated'),
		);
	}

/**
 * Initialize method
 *
 * Called before any other method in the decorator
 *
 * Just set the arguments as instance properties for easier access later
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function init(CakeEvent $event) {
		$this->_request = $event->subject->request;
		$this->_controller = $event->subject->controller;
	}

/**
 * Called before a record is saved in add or edit actions
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeSave(CakeEvent $event) {

	}

/**
 * Called before any CRUD redirection
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeRedirect(CakeEvent $event) {

	}

/**
 * Called before any find() on the model
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeFind(CakeEvent $event) {

	}

/**
 * After find callback
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterFind(CakeEvent $event) {

	}

/**
 * Called after any save() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterSave(CakeEvent $event) {

	}

/**
 * Called before cake's own render()
 *
 * CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeRender(CakeEvent $event) {

	}

/**
 * Called before any delete() action
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeDelete(CakeEvent $event) {

	}

/**
 * Called after any delete() action
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterDelete(CakeEvent $event) {

	}

/**
 * Called before related records list for a model is fetched.
 * `$event->subject` will contain the following properties that can be modified:
 *
 * - query: An array with options for find('list')
 * - model: Model instance, the model to be used for fiding the list or records
 *
 *  @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeListRelated(CakeEvent $event) {

	}

/**
 * Called after related records list for a model is fetched
 * `$event->subject` will contain the following properties that can be modified:
 *
 * - items: result from calling find('list')
 * - viewVar: Variable name to be set on the view with items as value
 * - model: Model instance, the model to be used for fiding the list or records
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterListRelated(CakeEvent $event) {

	}

/**
 * Called if a find() did not return any records
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function recordNotFound(CakeEvent $event) {

	}

/**
 * Called right before any paginate() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforePaginate(CakeEvent $event) {

	}

/**
 * Called right after any paginate() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterPaginate(CakeEvent $event) {

	}

/**
 * Called if the ID format validation failed
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function invalidId(CakeEvent $event) {

	}

/**
 * Called before any CakeSession::setFlash
 *
 * Subject contains the following keys you can modify:
 * 	- message
 * 	- element = 'default',
 * 	- params = array()
 * 	- key = 'flash'
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function setFlash(CakeEvent $event) {

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
 * @return mixed|CrudListener
 */
	public function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return $this->_settings;
		}

		if (empty($value)) {
			if (is_array($key)) {
				$this->_settings = Hash::merge($this->_settings, $key);
				return $this->_settings;
			}

			return Hash::get($this->_settings, $key);
		}

		if (is_array($value)) {
			$merge = Hash::get($this->_settings, $key);
			if ($merge) {
				$value += $merge;
			}
		}

		$this->_settings = Hash::insert($this->_settings, $key, $value);
		return $this;
	}

}
