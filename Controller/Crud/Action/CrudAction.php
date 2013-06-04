<?php
class CrudAction implements CakeEventListener {

	protected $_settings = array();

	public function implementedEvents() {
		return array(
			'Crud.handle'	=> array('callable' => 'handle')
		);
	}

	public function __construct(CrudSubject $subject) {
		$this->_Crud = $subject->crud;
		$this->_Collection = $subject->collection;
		$this->_request = $subject->request;

		$this->config('handleAction', $subject->handleAction);
	}

	public function handle(CakeEvent $event) {
		if ($event->subject->action !== $this->config('handleAction')) {
			return;
		}

		$subject = $event->subject;
		$this->_action = $subject->action;
		$this->_controller = $subject->controller;
		$this->_modelName = $subject->modelClass;
		$this->_model = $subject->model;

		return call_user_method_array('_handle', $this, $subject->args);
	}

	public function disable() {
		return $this->config('enabled', false);
	}

	public function enable() {
		return $this->config('enabled', true);
	}

	public function findMethod($method = null) {
		if (empty($method)) {
			return $this->config('findMethod');
		}

		return $this->config('findMethod', $method);
	}

	public function saveOptions($config = null) {
		if (empty($config)) {
			return $this->config('saveOptions');
		}

		return $this->config('saveOptions', $config);
	}

	public function view($view) {
		return $this->config('view', $view);
	}

/**
 * Get the model find method for a current controller action
 *
 * @param string|NULL $action The controller action
 * @param string|NULL $default The default find method in case it haven't been mapped
 * @return string The find method used in ->_model->find($method)
 */
	protected function _getFindMethod($default = null) {
		$findMethod = $this->config('findMethod');
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
 * Is the passed ID valid ?
 *
 * By default we assume you want to validate an numeric string
 * like a normal incremental ids from MySQL
 *
 * Change the validateId settings key to "uuid" for UUID check instead
 *
 * @param mixed $id
 * @return boolean
 */
	protected function _validateId($id) {
		$type = $this->config('validateId');

		if (empty($type)) {
			$type = $this->_detectPrimaryKeyFieldType();
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

		$subject = $this->_Crud->trigger('invalidId', compact('id'));
		$this->setFlash('invalid_id.error');
		return $this->_redirect($subject, $this->_controller->referer());
	}


/**
 * Automatically detect primary key data type for `_validateId()`
 *
 * Binary or string with length of 36 chars will be detected as UUID
 * If the primary key is a number, integer validation will be used
 *
 * If no reliable detection can be made, no validation will be made
 *
 * @return string
 */
	protected function _detectPrimaryKeyFieldType() {
		if (empty($this->_model) || empty($this->_modelName)) {
			$this->_setModelProperties();
		}

		$fInfo = $this->_model->schema($this->_model->primaryKey);
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
 * Build options for saveAll
 *
 * Merges defaults + any custom options for the specific action
 *
 * @param string|NULL $action
 * @return array
 */
	protected function _getSaveAllOptions($action = null) {
		$action = $action ?: $this->_action;
		return (array)$this->config('saveOptions');
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
		$subject = $this->_Crud->trigger('beforeRedirect', $subject);
		$url = $subject->url;

		$this->_controller->redirect($url);
		return $this->_controller->response;
	}

/**
 * Wrapper for Session::setFlash
 *
 * @param string $type Message type
 * @return void
 */
	public function setFlash($type) {
		$name = $this->_getResourceName();
		$this->_Crud->getListener('translations');

		// default values
		$message = $element = $key = null;
		$params = array();

		$subject = $this->_Crud->trigger('setFlash', compact('message', 'element', 'params', 'key', 'type', 'name'));
		$this->_Crud->Session->setFlash($subject->message, $subject->element, $subject->params, $subject->key);
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
		// Read out the action config
		if (is_null($key) && is_null($value)) {
			return $this->_settings;
		}

		// No value provided
		if (is_null($value)) {
			if (is_array($key)) {
				$this->_settings = $this->_settings + (array)$key;
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
 * Return the human name of the model
 *
 * By default it uses Inflector::humanize, but can be changed
 * using the "name" configuration property
 *
 * @return string
 */
	protected function _getResourceName() {
		if (empty($this->settings['name'])) {
			$this->settings['name']	= Inflector::humanize($this->_modelName);
		}

		return $this->settings['name'];
	}

}
