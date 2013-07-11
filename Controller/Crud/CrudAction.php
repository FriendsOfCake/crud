<?php

App::uses('CakeEventListener', 'Event');
App::uses('Validation', 'Utility');

/**
 * Base Crud class
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
abstract class CrudAction implements CakeEventListener {

/**
 * Action configuration
 *
 * @var array
 */
	protected $_settings = array();

/**
 * Reference to the Crud component
 *
 * @var CrudComponent
 */
	protected $_crud;

/**
 * Reference to the ComponentCollection
 *
 * @var ComponentCollection
 */
	protected $_collection;

/**
 * Reference to the CakeRequest
 *
 * @var CakeRequest
 */
	protected $_request;

/**
 * Reference to the controller
 *
 * @var Controller
 */
	protected $_controller;

/**
 * Reference to the model
 *
 * @var Model
 */
	protected $_model;

/**
 * The modelClass property from the Controller
 *
 * @var string
 */
	protected $_modelClass;

/**
 * Constructor
 *
 * @param CrudSubject $subject
 * @return void
 */
	public function __construct(CrudSubject $subject, $defaults = array()) {
		$this->_crud = $subject->crud;
		$this->_request = $subject->request;
		$this->_collection = $subject->controller->Components;
		$this->_controller = $subject->controller;
		$this->_model = $subject->model;
		$this->_modelClass = $subject->modelClass;

		// Mark that we will only handle this specific action if asked
		$this->_settings['action'] = $subject->action;

		if (!empty($defaults)) {
			$this->config($defaults);
		}
	}

/**
 * Handle callback
 *
 * Based on the requested controller action,
 * decide if we should handle the request or not.
 *
 * By returning false the handling is cancelled and the
 * execution flow continues
 *
 * @param CakeEvent $event
 * @return mixed
 */
	public function handle(CrudSubject $subject) {
		if (!$this->config('enabled')) {
			return false;
		}

		if ($subject->action !== $this->config('action')) {
			return false;
		}

		$this->_model = $subject->model;
		$this->_modelClass = $subject->modelClass;

		return call_user_func_array(array($this, '_handle'), $subject->args);
	}

/**
 * Disable the Crud action
 *
 * @return void
 */
	public function disable() {
		$this->config('enabled', false);

		$pos = array_search($this->_settings['action'], $this->_controller->methods);
		if (false !== $pos) {
			unset($this->_controller->methods[$pos]);
		}
	}

/**
 * Enable the Crud action
 *
 * @return void
 */
	public function enable() {
		$this->config('enabled', true);

		$pos = array_search($this->_settings['action'], $this->_controller->methods);
		if (false === $pos) {
			$this->_controller->methods[] = $this->_settings['action'];
		}
	}

/**
 * Change the find() method
 *
 * If `$method` is NULL the current value is returned
 * else the `findMethod` is changed
 *
 * @param mixed $method
 * @return mixed
 */
	public function findMethod($method = null) {
		if (empty($method)) {
			return $this->config('findMethod');
		}

		return $this->config('findMethod', $method);
	}

/**
 * return the config for a given message type
 *
 * @param string $type
 * @param array $replacements
 * @return array
 * @throws CakeException for a missing or undefined message type
 */
	public function message($type, $replacements = array()) {
		if (empty($type)) {
			throw new CakeException('Missing message type');
		}

		$config = $this->config('messages.' . $type);
		if (empty($config)) {
			$config = $this->_crud->config('messages.' . $type);
			if (empty($config)) {
				throw new CakeException(sprintf('Invalid message type "%s"', $type));
			}
		}

		if (is_string($config)) {
			$config = array('text' => $config);
		}

		$config = Hash::merge(array(
			'element' => 'default',
			'params' => array('class' => 'message'),
			'key' => 'flash',
			'type' => $this->config('action') . '.' . $type,
			'name' => $this->_getResourceName()
		), $config);

		if (!isset($config['text'])) {
			throw new CakeException(sprintf('Invalid message config for "%s" no text key found', $type));
		}

		$config['params']['original'] = ucfirst(
			str_replace('{name}', $config['name'], $config['text'])
		);

		$domain = $this->config('messages.domain');
		if (!$domain) {
			$domain = $this->_crud->config('messages.domain') ?: 'crud';
		}

		$config['text'] = __d($domain, $config['params']['original']);

		$config['text'] = String::insert(
			$config['text'],
			$replacements + array('name' => $config['name']),
			array('before' => '{', 'after' => '}')
		);

		$config['params']['class'] .= ' ' . $type;

		return $config;
	}

/**
 * Change the saveOptions configuration
 *
 * This is the 2nd argument passed to saveAll()
 *
 * if `$config` is NULL the current config is returned
 * else the `saveOptions` is changed
 *
 * @param mixed $config
 * @return mixed
 */
	public function saveOptions($config = null) {
		if (empty($config)) {
			return $this->config('saveOptions');
		}

		return $this->config('saveOptions', $config);
	}

/**
 * Change the view to be rendered
 *
 * If `$view` is NULL the current view is returned
 * else the `$view` is changed
 *
 * If no view is configured, it will use the action
 * name from the request object
 *
 * @param mixed $view
 * @return mixed
 */
	public function view($view = null) {
		if (empty($view)) {
			return $this->config('view') ?: $this->_request->action;
		}

		return $this->config('view', $view);
	}

/**
 * List of implemented events
 *
 * @return array
 */
	public function implementedEvents() {
		return array();
	}

/**
 * Sets a configuration variable into this action
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

/**
 * Get the model find method for a current controller action
 *
 * @param string|NULL $action The controller action
 * @param string|NULL $default The default find method in case it haven't been mapped
 * @return string The find method used in ->_model->find($method)
 */
	protected function _getFindMethod($default = null) {
		$findMethod = $this->findMethod();
		if (!empty($findMethod)) {
			return $findMethod;
		}

		return $default;
	}

/**
 * Helper method to get the passed ID to an action
 *
 * @return string
 */
	public function getIdFromRequest() {
		if (empty($this->_request->params['pass'][0])) {
			return null;
		}

		return $this->_request->params['pass'][0];
	}

/**
 * Wrapper for Session::setFlash
 *
 * @param string $type Message type
 * @return void
 */
	public function setFlash($type) {
		$config = $this->message($type);

		$subject = $this->_crud->trigger('setFlash', $config);
		if (!empty($subject->stopped)) {
			return;
		}

		$this->_crud->Session->setFlash($subject->text, $subject->element, $subject->params, $subject->key);
	}

/**
 * Automatically detect primary key data type for `_validateId()`
 *
 * Binary or string with length of 36 chars will be detected as UUID
 * If the primary key is a number, integer validation will be used
 *
 * If no reliable detection can be made, no validation will be made
 *
 * @param NULL|Model $model
 * @return string
 * @throws CakeException If unable to get model object
 */
	public function detectPrimaryKeyFieldType($model = null) {
		if (empty($model)) {
			if (empty($this->_model)) {
				throw new CakeException('Missing model object, cant detect primary key field type');
			}

			$model = $this->_model;
		}

		$fInfo = $model->schema($model->primaryKey);
		if (empty($fInfo)) {
			return false;
		}

		if ($fInfo['length'] == 36 && ($fInfo['type'] === 'string' || $fInfo['type'] === 'binary')) {
			return 'uuid';
		}

		if ($fInfo['type'] === 'integer') {
			return 'integer';
		}

		return false;
	}

/**
 * Return the human name of the model
 *
 * By default it uses Inflector::humanize, but can be changed
 * using the "name" configuration property
 *
 * @return string
 */
	protected function _getResourceName() {
		if (empty($this->_settings['name'])) {
			$this->_settings['name'] = strtolower(Inflector::humanize(Inflector::underscore($this->_modelClass)));
		}

		return $this->_settings['name'];
	}

/**
 * Is the passed ID valid ?
 *
 * By default we assume you want to validate an numeric string
 * like a normal incremental ids from MySQL
 *
 * Change the validateId settings key to "uuid" for UUID check instead
 *
 * @param mixed $id
 * @return boolean
 * @throws BadRequestException If id is invalid
 */
	protected function _validateId($id) {
		$type = $this->config('validateId');

		if (empty($type)) {
			$type = $this->detectPrimaryKeyFieldType();
		}

		if (!$type) {
			return true;
		} elseif ($type === 'uuid') {
			$valid = Validation::uuid($id);
		} else {
			$valid = is_numeric($id);
		}

		if ($valid) {
			return true;
		}

		$subject = $this->_crud->trigger('invalidId', compact('id'));

		$message = $this->message('invalidId');
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
	}

/**
 * Called for all redirects inside CRUD
 *
 * @param CrudSubject $subject
 * @param array|null $url
 * @return void
 */
	protected function _redirect($subject, $url = null) {
		if (!empty($this->_request->data['redirect_url'])) {
			$url = $this->_request->data['redirect_url'];
		} elseif (!empty($this->_request->query['redirect_url'])) {
			$url = $this->_request->query['redirect_url'];
		} elseif (empty($url)) {
			$url = array('action' => 'index');
		}

		$subject->url = $url;
		$subject = $this->_crud->trigger('beforeRedirect', $subject);
		$url = $subject->url;

		$this->_controller->redirect($url);
		return $this->_controller->response;
	}

/**
 * Implements all the request handling and response serving logic
 * for this action
 *
 * @return CakeResponse
 */
	protected abstract function _handle();

}
