<?php

App::uses('Hash', 'Utility');
App::uses('CakeEventListener', 'Event');
App::uses('CrudListener', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * TranslationsEvent for Crud
 *
 * Handles all translations inside Crud and friends
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class TranslationsListener extends CrudListener implements CakeEventListener {

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
				'message' => 'Successfully created {name}'
			),
			'error' => array(
				'message' => 'Could not create {name}'
			)
		),
		'update' => array(
			'success' => array(
				'message' => '{name} was successfully updated'
			),
			'error' => array(
				'message' => 'Could not update {name}'
			)
		),
		'delete' => array(
			'success' => array(
				'message' => 'Successfully deleted {name}'
			),
			'error' => array(
				'message' => 'Could not delete {name}'
			)
		),
		'find' => array(
			'error' => array(
				'message' => 'Could not find {name}'
			)
		),
		'invalid_http_request' => array(
			'error' => array(
				'message' => 'Invalid HTTP request'
			)
		),
		'invalid_id' => array(
			'error' => array(
				'message' => 'Invalid id'
			)
		)
	);

/**
 * Class constructor
 *
 * @param string $prefix CRUD component events name prefix
 * @param array $models List of models to be fetched in beforeRenderEvent
 * @return void
 */
	public function __construct(CrudSubject $subject, $defaults = array()) {
		$this->_settings = $this->_defaults;
		parent::__construct($subject, $defaults);
	}

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		return array('Crud.setFlash' => array('callable' => 'setFlash', 'priority' => 5));
	}

	public function getDefaults() {
		return $this->_defaults;
	}

/**
 * SetFlash Crud Event callback
 *
 * @param CakeEvent $e
 * @return void
 * @throws CakeException If called with invalid args
 */
	public function setFlash(CakeEvent $event) {
		if (empty($event->subject->type)) {
			throw new CakeException('Missing flash type');
		}

		$type = $event->subject->type;

		$config = Hash::get($this->_settings, $type);
		if (empty($config)) {
			throw new CakeException('Invalid flash type');
		}

		$name = $this->config('name') ?: $event->subject->name;
		$config = Hash::merge(array(
			'message' => null,
			'element' => 'default',
			'params' => array('class' => 'message'),
			'key' => 'flash'
		), $config);

		$type = explode('.', $type);
		$config['params']['class'] .= ' ' . array_pop($type);

		$message = String::insert(
			$config['message'],
			array('name' => $name),
			array('before' => '{', 'after' => '}')
		);

		$event->subject->message = __d($this->config('domain'), $message);
		$event->subject->element = $config['element'];
		$event->subject->params = $config['params'];
		$event->subject->key = $config['key'];
	}

}
