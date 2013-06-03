<?php
class CrudAction implements CakeEventListener {

	public function implementedEvents() {
		return array(
			'Crud.handle'	=> array('callable' => 'handle')
		);
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
		$this->_settings	 = $subject->config;

		return $this->_handle();
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

		$findMethod = $this->_Crud->config(sprintf('findMethodMap.%s', $action));
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
		$type = $this->_Crud->config('validateId');

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
		$this->_Crud->setFlash('invalid_id.error');
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
		return (array)$this->_Crud->config('saveOptions');
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
}
