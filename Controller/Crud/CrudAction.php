<?php

App::uses('CrudBaseObject', 'Crud.Controller/Crud');
App::uses('Hash', 'Utility');
App::uses('Validation', 'Utility');

/**
 * Base Crud class
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class CrudAction extends CrudBaseObject {

/**
 * Startup method
 *
 * Called when the action is loaded
 *
 * @param CrudSubject $subject
 * @param array $defaults
 * @return void
 */
	public function __construct(CrudSubject $subject, $defaults = array()) {
		parent::__construct($subject, $defaults);

		$this->_settings['action'] = $subject->action;
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
 * @throws NotImplementedException if the action can't handle the request
 * @param CakeEvent $event
 * @return mixed
 */
	public function handle(CrudSubject $subject) {
		if (!$this->config('enabled')) {
			return false;
		}

		$requestMethod = $this->_request()->method();
		$method = '_' . strtolower($requestMethod);

		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $subject->args);
		}

		if (method_exists($this, '_handle')) {
			return call_user_func_array(array($this, '_handle'), $subject->args);
		}

		throw new NotImplementedException(sprintf('Action %s does not implement a handler for HTTP verb %s', get_class($this), $requestMethod));
	}

/**
 * Disable the Crud action
 *
 * @return void
 */
	public function disable() {
		$this->config('enabled', false);

		$Controller = $this->_controller();
		$actionName = $this->config('action');

		$pos = array_search($actionName, $Controller->methods);
		if (false !== $pos) {
			unset($Controller->methods[$pos]);
		}
	}

/**
 * Enable the Crud action
 *
 * @return void
 */
	public function enable() {
		$this->config('enabled', true);

		$Controller = $this->_controller();
		$actionName = $this->config('action');

		$pos = array_search($actionName, $Controller->methods);
		if (false === $pos) {
			$Controller->methods[] = $actionName;
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

		$crud = $this->_crud();

		$config = $this->config('messages.' . $type);
		if (empty($config)) {
			$config = $crud->config('messages.' . $type);
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

		$config['params']['original'] = ucfirst(str_replace('{name}', $config['name'], $config['text']));

		$domain = $this->config('messages.domain');
		if (!$domain) {
			$domain = $crud->config('messages.domain') ?: 'crud';
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
			return $this->config('view') ?: $this->_request()->action;
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
 * Get the model find method for a current controller action
 *
 * @param string|NULL $default The default find method in case it hasn't been mapped
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
 * Wrapper for Session::setFlash
 *
 * @param string $type Message type
 * @return void
 */
	public function setFlash($type) {
		$config = $this->message($type);

		$subject = $this->_trigger('setFlash', $config);
		if (!empty($subject->stopped)) {
			return;
		}

		$this->_session()->setFlash($subject->text, $subject->element, $subject->params, $subject->key);
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
			$model = $this->_model();
			if (empty($model)) {
				throw new CakeException('Missing model object, cant detect primary key field type');
			}
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
			$this->_settings['name'] = strtolower(Inflector::humanize(Inflector::underscore($this->_model()->name)));
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

		$subject = $this->_trigger('invalidId', compact('id'));

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
	protected function _redirect($subject, $url = null, $status = null, $exit = true) {
		$request = $this->_request();
		if (!empty($request->data['redirect_url'])) {
			$url = $request->data['redirect_url'];
		} elseif (!empty($request->query['redirect_url'])) {
			$url = $request->query['redirect_url'];
		} elseif (empty($url)) {
			$url = array('action' => 'index');
		}

		$subject->url = $url;
		$subject->status = $status;
		$subject->exit = $exit;
		$subject = $this->_trigger('beforeRedirect', $subject);

		$controller = $this->_controller();
		$controller->redirect($subject->url, $subject->status, $subject->exit);
		return $controller->response;
	}

}
