<?php
class CrudAction implements CakeEventListener {

	protected $_settings = array();

	public function implementedEvents() {
		return array(
			'Crud.init'	=> array('callable' => 'init'),
			'Crud.handle'	=> array('callable' => 'handle')
		);
	}

	public function init(CakeEvent $event) {
		$subject 					 = $event->subject;
		$this->_Crud 			 = $subject->crud;
		$this->_action		 = $subject->action;
	}

	public function handle(CakeEvent $event) {
		$subject 					 = $event->subject;

		$this->_Crud 			 = $subject->crud;
		$this->_action		 = $subject->action;
		$this->_Collection = $subject->collection;
		$this->_controller = $subject->controller;
		$this->_modelName  = $subject->modelClass;
		$this->_model 		 = $subject->model;
		$this->_request 	 = $subject->request;

		if (!array_key_exists($subject->action, $this->_settings)) {
			return;
		}

		return $this->_handle();
	}

	public function disable($action) {
		return $this->config('enabled', false, $action);
	}

	public function enable($action) {
		return $this->config('enabled', true, $action);
	}

	public function findMethod($action, $method) {
		return $this->config('findMethod', $method, $action);
	}

/**
 * Get the model find method for a current controller action
 *
 * @param string|NULL $action The controller action
 * @param string|NULL $default The default find method in case it haven't been mapped
 * @return string The find method used in ->_model->find($method)
 */
	protected function _getFindMethod($action = null, $default = null) {
		if (empty($action)) {
			$action = $this->_action;
		}

		$findMethod = $this->_Crud->config('findMethod', null, $action);
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
 * @param mixed $action
 * @return TranslationsEvent
 */
	public function config($key = null, $value = null, $action = null) {
		// No action parameter = current action
		if (is_null($action)) {
			$action = $this->_action;
		}

		debug($action);

		// Read out the action config
		if (is_null($key) && is_null($value)) {
			return $this->_settings[$action];
		}

		// No value provided
		if (is_null($value)) {
			if (is_array($key)) {
				$this->_settings[$action] = $this->_settings[$action] + (array)$key;
				return $this;
			}

			return Hash::get($this->_settings[$action], $key);
		}

		debug($value);
		if (is_array($value)) {
			$value = $value + (array)Hash::get($this->_settings[$action], $key);
		}
		debug($value);

		// Ensure action key exist
		if (!array_key_exists($action, $this->_settings)) {
			$this->_settings[$action] = array();
		}

		$this->_settings[$action] = Hash::insert($this->_settings[$action], $key, $value);
		debug(get_class($this));
		debug($key);
		debug($action);
		debug($this->_settings);
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
