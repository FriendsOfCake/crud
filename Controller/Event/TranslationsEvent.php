<?php

App::uses('CrudBaseEvent', 'Crud.Controller/Event');

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
class TranslationsEvent extends CrudBaseEvent {

/**
 * Configurations for TranslationsEvent
 *
 * @var array
 */
	protected $_config = array();

/**
 * Constructor
 *
 * Initializes default translations and merge them with
 * user supplied user configurations
 *
 * @param array $config
 * @return void
 */
	public function __construct($config = array()) {
		$defaults = array(
			'create' => array(
				'success' => array(
					'message' => __d('crud', 'Successfully created {name}'),
					'element' => 'success'
				),
				'error' => array(
					'message' => __d('crud', 'Could not create {name}'),
					'element' => 'error'
				)
			),

			'update' => array(
				'success' => array(
					'message' => __d('crud', '{name} was successfully updated'),
					'element' => 'success'
				),
				'error' => array(
					'message' => __d('crud', 'Could not update {name}'),
					'element' => 'error'
				)
			),

			'delete' => array(
				'success' => array(
					'message' => __d('crud', 'Successfully deleted {name}'),
					'element' => 'success'
				),
				'error' => array(
					'message' => __d('crud', 'Could not delete {name}'),
					'element' => 'error'
				)
			),

			'find' => array(
				'error' => array(
					'message' => __d('crud', 'Could not find {name}'),
					'element' => 'error'
				)
			),

			'error' => array(
				'invalid_http_request' => array(
					'message' => __d('crud', 'Invalid HTTP request'),
					'element' => 'error'
				),
				'invalid_id' => array(
					'message' => __d('crud', 'Invalid id'),
					'element' => 'error'
				)
			)
		);

		$this->_config = $defaults + $config;
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
				$this->_config = $this->_config + $key;
				return $this;
			}

			return Hash::get($this->_config, $key);
		}

		if (is_array($value)) {
			$value = $value + Hash::get($this->_config, $key);
		}

		$this->_config = Hash::insert($this->_config, $key, $value);
		return $this;
	}

/**
 * SetFlash Crud Event callback
 *
 * @param CakeEvent $e
 * @return void
 */
	public function setFlash(CakeEvent $event) {
		if (empty($event->subject->type)) {
			throw new CakeException(__d('crud', 'Missing flash type'));
		}

		$type = $event->subject->type;

		$config = Hash::get($this->_config, $type);
		if (empty($config)) {
			throw new CakeException(__d('crud', 'Invalid flash type'));
		}

		$config = $config + array('message' => null, 'element' => null, 'params' => array(), 'key' => 'flash');

		$event->subject->message = String::insert($config['message'], array('name' => $event->subject->name), array('before' => '{', 'after' => '}'));
		$event->subject->element = $config['element'];
		$event->subject->params = $config['params'];
		$event->subject->key = $config['key'];
	}

}
