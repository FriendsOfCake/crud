<?php
class CrudComponent extends Component {
	/**
	* List of form callbacks for CRUD actions
	*
	* Each key is the one of the CRUD controller action
	*
	* Each value is a key / value pair of class => configuration items
	* Each class must extend BaseFormDecorator
	*
	* @platform
	* @var array
	*/
	protected $_callbacks = array(
		// Public callbacks
		'index'			=> array(),
		'add'			=> array(),
		'edit'			=> array(),
		'view'			=> array(),
		'delete'		=> array(),

		// Admin callbacks
		'admin_index'	=> array(),
		'admin_add'		=> array(),
		'admin_edit'	=> array(),
		'admin_view'	=> array(),
		'admin_delete'	=> array(),

		// Shared callbacks
		'common'		=> array(
			'Crud.Default'
		)
	);

	/**
	* A map of the controller action and what CRUD action we should call
	*
	* By default it supports non-prefix and admin_ prefixed routes
	*
	* @platform
	* @var array
	*/
	protected $_actionMap = array(
		'index'			=> 'index',
		'add'			=> 'add',
		'edit'			=> 'edit',
		'view'			=> 'view',
		'delete'		=> 'delete',

		'admin_index'	=> 'index',
		'admin_add'		=> 'add',
		'admin_edit'	=> 'edit',
		'admin_view'	=> 'view',
		'admin_delete'	=> 'delete'
	);

	/**
	* A map of the controller action and the view to render
	*
	* * By default it supports non-prefix and admin_ prefixed routes
	*
	* @platform
	* @var array
	*/
	protected $_viewMap = array(
		'index'			=> 'index',
		'add'			=> 'form',
		'edit'			=> 'form',
		'view'			=> 'view',

		'admin_index'	=> 'admin_index',
		'admin_add'		=> 'admin_form',
		'admin_edit'	=> 'admin_form',
		'admin_view'	=> 'admin_view'
	);

	/**
	* Enable a CRUD action
	*
	* @platform
	* @param string $action The action to enable
	* @return void
	*/
	public function enableAction($action) {
		$pos = array_search($action, $this->settings['actions']);
		if (false === $pos) {
			$this->settings['actions'][] = $action;
		}
	}

	/**
	* Disable a CRUD action
	*
	* @platform
	* @param string $action The action to disable
	* @return void
	*/
	public function disableAction($action) {
		$pos = array_search($action, $this->settings['actions']);
		if (false !== $pos) {
			unset($this->settings['actions'][$pos]);
		}
	}

	/**
	* Map the view file to use for a controller action
	*
	* To map multiple action views in one go pass an array as first argument and no second argument
	*
	* @platform
	* @param string|array $action
	* @param string $view
	* @return void
	*/
	public function mapActionView($action, $view = null) {
		if (is_array($action)) {
			$this->_viewMap = $this->_viewMap + $action;
			return;
		}

		$this->_viewMap[$action] = $view;
	}

	/**
	* Add a callback class to CRUD component
	*
	* @platform
	* @param string $action The controller action
	* @param string $class  The callback class
	* @param array  $config Configuration for the callback configuration
	* @return void
	*/
	public function addCallback($action, $class, $config = array()) {
		$this->_callbacks[$action][$class] = $config;
	}

	/**
	* Clear all callbacks for an action
	*
	* @platform
	* @param string $action 	The controller action to clear the callbacks for
	* @param boolean $common	Also clear the common callback
	* @return void
	*/
	public function clearCallbacks($action, $common = false) {
		$this->_callbacks[$action] = array();
		if ($common) {
			$this->_callbacks['common'] = array();
		}
	}

	/**
	* The startup method is called after the controller’s beforeFilter method
	* but before the controller executes the current action handler.
	*
	* We catch our CRUD actions here before Cake complains about them
	* not existing in the Controller
	*
	* @cakephp
	* @param Controller $controller
	* @return void
	*/
	public function startup(Controller $controller) {
		// Create some easy accessible class properties
		$this->action 		= $controller->action;
		$this->controller	= $controller;
		$this->request 		= $controller->request;

		// Don't do anything if the action is defined in the controller already
		if (method_exists($controller, $this->action)) {
			return;
		}

		// Don't do anything if the action haven't been marked as CRUD
		if (!in_array($this->action, $this->settings['actions'])) {
			return;
		}

		// Don't do anything if the action isn't mapped
		if (!array_key_exists($this->action, $this->_actionMap)) {
			return;
		}

		// Execute the default action, inside this component
		call_user_func(array($this, $this->_actionMap[$this->action] . 'Action'));

		$view = $this->action;
		if (array_key_exists($this->action, $this->_viewMap)) {
			$view = $this->_viewMap[$this->action];
		}

		// Render the file based on action name
		$content = $this->controller->render($view);

		// Send the content to the browser
		$content->send();

		// Stop the request
		$this->_stop();
	}

	/**
	* Initialize properties needed for CRUD behavior
	*
	* @platform
	* @return void
	*/
	protected function _setup() {
		$this->_loadCallbackCollection();

		$this->modelClassName 	= $this->controller->modelClass;
		$this->modelClass		= $this->controller->{$this->modelClassName};
	}

	/**
	* Helper method to get the passed ID to an action
	*
	* @platform
	* @return string
	*/
	protected function _getIdFromRequest() {
		if (empty($this->request->params['pass'][0])) {
			return null;
		}
		return $this->request->params['pass'][0];
	}

	/**
	* Generic index action
	*
	* Triggers the following callbacks
	*  - beforePaginate
	*  - beforeRender
	*
	* @platform
	* @param string $id
	* @return void
	*/
	public function indexAction() {
		$this->_setup();
		$this->collection->trigger('beforePaginate', array($this->controller));

		$items = $this->controller->paginate();
		$this->controller->set(compact('items'));

		$this->collection->trigger('beforeRender');
	}

	/**
	* Generic add action
	*
	* Triggers the following callbacks
	*  - beforeSave
	*  - afterSave
	*  - beforeRender
	*
	* @platform
	* @param string $id
	* @return void
	*/
	public function addAction() {
		$this->_setup();
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->collection->trigger('beforeSave');
			if ($this->modelClass->saveAll($this->request->data, array('validate' => 'first', 'atomic' => true))) {
				$this->collection->trigger('afterSave', array(true, $this->modelClass->id));

				$this->Session->setFlash(__d('common', 'Succesfully created %s', Inflector::humanize($this->modelClassName)), 'flash/success');
				$this->controller->redirect(array('action' => 'index'));
			} else {
				$this->collection->trigger('afterSave', array(false));
				$this->Session->setFlash(__d('common', 'Could not create %s', Inflector::humanize($this->modelClassName)), 'flash/error');
			}
		}

		$this->collection->trigger('beforeRender');
	}

	/**
	* Generic edit action
	*
	* Triggers the following callbacks
	*  - beforeSave
	*  - afterSave
	*  - beforeFind
	*  - recordNotFound
	*  - afterFind
	*  - beforeRender
	*
	* @platform
	* @param string $id
	* @return void
	*/
	public function editAction($id = null) {
		$this->_setup();
		$this->validateUUID($id);

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->collection->trigger('beforeSave');
			if ($this->modelClass->saveAll($this->request->data, array('validate' => 'first', 'atomic' => true))) {
				$this->collection->trigger('afterSave', array(true, $this->modelClass->id));

				$this->Session->setFlash(__d('common', '%s was succesfully updated', ucfirst(Inflector::humanize($this->modelClassName))), 'flash/success');
				$this->controller->redirect(array('action' => 'index'));
			} else {
				$this->collection->trigger('afterSave', array(false));
				$this->Session->setFlash(__d('common', 'Could not update %s', Inflector::humanize($this->modelClassName)), 'flash/error');
			}
		} else {
			$query = array();
			$query['conditions'] = array($this->modelClass->escapeField() => $id);
			$query = $this->collection->trigger('beforeFind', array($query));

			$this->request->data = $this->modelClass->find('first', $query);
			if (empty($this->request->data)) {
				$this->collection->trigger('recordNotFound', array($id));
				$this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->modelClassName)), 'flash/error');
				$this->controller->redirect(array('action' => 'index'));
			}
			$this->request->data = $this->collection->trigger('afterFind', array($this->request->data));
		}
	}

	/**
	* Generic view action
	*
	* Triggers the following callbacks
	*  - beforeFind
	*  - recordNotFound
	*  - afterFind
	*  - beforeRender
	*
	* @platform
	* @param string $id
	* @return void
	*/
	public function viewAction($id = null) {
		$this->_setup();
		if (empty($id)) {
			$id = $this->_getIdFromRequest();
		}

		// Build conditions
		$query = array();
		$query['conditions'] = array($this->modelClass->escapeField() => $id);
		$query = $this->collection->trigger('beforeFind', array($query));

		// Try and find the database record
		$item = $this->modelClass->find('first', $query);

		// We could not find any record match the conditions in query
		if (empty($item)) {
			$this->collection->trigger('recordNotFound', array($id));

			$this->controller->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->modelClassName)), 'flash/error');
			$this->controller->redirect(array('action' => 'index'));
		}

		// We found a record, trigger an afterFind
		$item = $this->collection->trigger('afterFind', array($item));

		// Push it to the view
		$this->controller->set(compact('item'));

		// Trigger a beforeRender
		$this->collection->trigger('beforeRender');
	}

	/**
	* Generic delete action
	*
	* Triggers the following callbacks
	*  - beforeFind
	*  - recordNotFound
	*  - beforeDelete
	*  - afterDelete
	*
	* @platform
	* @param string $id
	* @return void
	*/
	public function deleteAction($id = null) {
		$this->_setup();
		if (empty($id)) {
			$id = $this->_getIdFromRequest();
		}

		$this->validateUUID($id);
		$query = array();
		$query['conditions'] = array($this->modelClass->escapeField() => $id);
		$query = $this->collection->trigger('beforeFind', array($query));

		$count = $this->modelClass->find('count', $query);
		if (empty($count)) {
			$this->collection->trigger('recordNotFound', array($id));
			$this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->modelClassName)), 'flash/error');
			$this->controller->redirect(array('action' => 'index'));
		}

		$item = $this->collection->trigger('beforeDelete', array($id));

		if ($this->modelClass->delete($id)) {
			$this->collection->trigger('afterDelete', array($id, true));
		} else {
			$this->collection->trigger('afterDelete', array($id, false));
		}

		$this->controller->redirect(array('action' => 'index'));
	}

	/**
	* Get the callback collection
	*
	* @platform
	* @param string $action
	* @return void
	*/
	protected function _loadCallbackCollection($action = null) {
		if (empty($action)) {
			$action = $this->action;
		}

		// Initialize Collection and load callbacks
		$this->collection =new CrudCollection();
		$this->collection->loadAll($this->_callbacks['common']);
		$this->collection->loadAll($this->_callbacks[$action]);

		// If the Api plugin has been loaded and we are in API request, load Api callback
		if (CakePlugin::loaded('Api') && $this->request->is('api')) {
			$this->collection->load('Api.Api');
		}

		// Call _loadCallbackCollections if the method exists in the controller
		if (method_exists($this->controller, '_loadCallbackCollections')) {
			$this->collection->loadAll($this->controller->_loadCallbackCollections($action));
		}

		// Trigger init on the Collection
		$this->collection->trigger('init', array($this->controller, $action));
	}

	/**
	* Is this an admin request?
	*
	* @return boolean
	*/
	public function isAdmin() {
		return isset($this->request->params['admin']) && $this->request->params['admin'];
	}
}