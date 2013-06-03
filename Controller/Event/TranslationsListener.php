<?php

App::uses('CakeEventListener', 'Event');
App::uses('CrudSubject', 'Crud.Controller/Event');

/**
 * TranslationsEvent for Crud
 *
 * Handles all translations inside Crud and friends
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @see http://book.cakephp.org/2.0/en/controllers/components.html#Component
 * @copyright Nodes ApS, 2012
 */
class TranslationsListener implements CakeEventListener {

/**
 * Configurations for TranslationsEvent
 *
 * @var array
 */
	protected $_config = array();

/**
 * _defaults
 *
 * `domain` the translation domain to be used
 * `name` the name to use in flash messages - defaults to the model's name property
 * `event-name` the remaining array keys are event-name indexed arrays with the flash-message
 * settings for each event
 *
 * @var array
 */
	protected $_defaults = array(
		'domain' => 'crud',
		'name' => null,
		'create' => array(
			'success' => array(
				'message' => 'Successfully created {name}',
				'element' => 'success'
			),
			'error' => array(
				'message' => 'Could not create {name}',
				'element' => 'error'
			)
		),
		'update' => array(
			'success' => array(
				'message' => '{name} was successfully updated',
				'element' => 'success'
			),
			'error' => array(
				'message' => 'Could not update {name}',
				'element' => 'error'
			)
		),
		'delete' => array(
			'success' => array(
				'message' => 'Successfully deleted {name}',
				'element' => 'success'
			),
			'error' => array(
				'message' => 'Could not delete {name}',
				'element' => 'error'
			)
		),
		'find' => array(
			'error' => array(
				'message' => 'Could not find {name}',
				'element' => 'error'
			)
		),
		'invalid_http_request' => array(
			'error' => array(
				'message' => 'Invalid HTTP request',
				'element' => 'error'
			)
		),
		'invalid_id' => array(
			'error' => array(
				'message' => 'Invalid id',
				'element' => 'error'
			)
		)
	);

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
 * Class constructor
 *
 * @param string $prefix CRUD component events name prefix
 * @param array $models List of models to be fetched in beforeRenderEvent
 * @return void
 */
	public function __construct(CrudSubject $subject) {
		$this->_subject = $subject;
		$this->_config = $this->_defaults;

		if (!isset($subject->crud)) {
			return;
		}

		$this->_crud = $subject->crud;
		if ($translations = $this->_crud->config('translations')) {
			$this->config($translations);
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
			'Crud.setFlash' => array('callable' => 'setFlash', 'priority' => 5)
		);
	}

/**
 * Generic config method
 *
 * If $key is an array and $value is empty,
 * $key will be merged directly with $this->_config
 *
 * If $key is a string it will be passed into Hash::insert
 *
 * @param mixed $key
 * @param mixed $value
 * @return TranslationsEvent
 */
	public function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return $this->_config;
		}

		if (empty($value)) {
			if (is_array($key)) {
				$this->_config = Hash::merge($this->_config, $key);
				return $this->_config;
			}

			return Hash::get($this->_config, $key);
		}

		if (is_array($value)) {
			$merge = Hash::get($this->_config, $key);
			if ($merge) {
				$value += $merge;
			}
		}

		$this->_config = Hash::insert($this->_config, $key, $value);
		return $this;
	}

	public function getDefaults() {
		return $this->_defaults;
	}

/**
 * SetFlash Crud Event callback
 *
 * @throws CakeException if called with invalid args
 * @param CakeEvent $e
 * @return void
 */
	public function setFlash(CakeEvent $event) {
		if (empty($event->subject->type)) {
			throw new CakeException('Missing flash type');
		}

		$type = $event->subject->type;

		$config = Hash::get($this->_config, $type);
		if (empty($config)) {
			throw new CakeException('Invalid flash type');
		}

		$name = $this->_config['name'] ?: $event->subject->name;
		$config += array('message' => null, 'element' => null, 'params' => array(), 'key' => 'flash');
		$message = String::insert($config['message'], array('name' => $name), array('before' => '{', 'after' => '}'));

		$event->subject->message = __d($this->_config['domain'], $message);
		$event->subject->element = $config['element'];
		$event->subject->params = $config['params'];
		$event->subject->key = $config['key'];
	}

}
