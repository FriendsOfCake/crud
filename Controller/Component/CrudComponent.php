<?php
App::uses('CrudEventSubject', 'Crud.Controller/Event');

/**
 * Crud component
 *
 * Handles the automatic transformation of HTTP requests to API responses
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @see https://wiki.ournodes.com/display/platform/Api+Plugin
 * @see http://book.cakephp.org/2.0/en/controllers/components.html#Component
 * @copyright Nodes ApS, 2012
 */
class CrudComponent extends Component {

	/**
	 * Reference to a Session component
	 *
	 * @cakephp
	 * @var array
	 */
	public $components = array('Session');

	/**
	 * The current controller action
	 *
	 * @platform
	 * @var string
	 */
	protected $_action;

	/**
	 * Reference to the current controller
	 *
	 * @platform
	 * @var Controller
	 */
	protected $_controller;

	/**
	 * Reference to the current request
	 *
	 * @platform
	 * @var CakeRequest
	 */
	protected $_request;

	/**
	 * Reference to the current event manager
	 *
	 * @platform
	 * @var CakeEventManager
	 */
	protected $_eventManager;

	/**
	* Cached property for Controller::modelClass
	*
	* @platform
	* @var string
	*/
	protected $_modelName;

	/**
	* Cached propety for the current Controller::modelClass instance
	*
	* @platform
	* @var Model
	*/
	protected $_model;

	/**
	* All emitted events will be prefixed with this property value
	*
	* @platform
	* @var string
	*/
	protected $_eventPrefix = 'Crud';

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
	 * By default it supports non-prefix and admin_ prefixed routes
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
	 * Components settings.
	 *
	 * `actions` key should contain an array of controller methods this component should offer
	 * implementation for.
	 *
	 * `relatedList` is a map of the controller action and the whether it should fetch associations lists
	 * to be used in select boxes. An array as value means it is enabled and represent the list
	 * of model associations to be fetched
	 *
	 * @var array
	 */
	public $settings = array(
		'actions' => array(),
		'relatedLists' => array(
			'add' => true,
			'edit' => true
		)
	);

	/**
	 * Name of the event listener class to be used for fetching related models list
	 * Class will be lokked up in Controller/Event package
	 *
	 * @var string
	 */
	protected $_relatedListEventClass = 'Crud.RelatedModelsListener';

	/**
	 * Constructor
	 *
	 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
	 * @param array $settings Array of configuration settings.
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings + $this->settings);
	}

	/**
	* The default find method for reading data
	*
	* Model->find($method)
	*
	* @platform
	* @var array
	*/
	protected $_findMethodMap = array(
		'index'			=> 'all',
		'edit'			=> 'first',
		'view'			=> 'first',
		'delete'		=> 'count',

		'admin_index'	=> 'all',
		'admin_edit'	=> 'first',
		'admin_view'	=> 'first',
		'admin_delete'	=> 'count'
	);

	/**
	 * Make sure to update the list of known controller methods before startup is called
	 *
	 * The reason for this is that if we don't, the Auth component won't execute any callbacks on the controller
	 * like isAuthorized
	 *
	 * @param Controller $controller
	 * @return void
	 */
	public function initialize(Controller $controller) {
		if ($controller->name == 'CakeError') {
			return true;
		}

		$this->_controller = $controller;
		$this->_controller->methods = array_keys(array_flip($this->_controller->methods) + array_flip($this->settings['actions']));

		// Create some easy accessible class properties
		$this->_action		= $this->_controller->request->action;
		$this->_request		= $this->_controller->request;
		$this->_eventManager= $this->_controller->getEventManager();

		if (!isset($this->_controller->dispatchComponents)) {
			$this->_controller->dispatchComponents = array();
		}

		$name = str_replace('Component', '', get_class($this));
		$this->_controller->dispatchComponents[$name] = true;
	}

	/**
	* Execute a Crud action
	*
	* @platform
	* @param string $action		The CRUD action
	* @param array $arguments	List of arguments to pass to the CRUD action (Usually an ID to edit / delete)
	* @return void
	*/
	public function executeAction($action = null, $args = array()) {
		$view = $action = $action ?: $this->_action;
		$this->_setModelProperties();

		// Make sure to update internal action property
		$this->_action = $action;

		$this->trigger('init');

		// Test if action is mapped
		if (empty($this->_actionMap[$action])) {
			throw new RuntimeException(sprintf('Action "%s" has not been mapped', $action));
		}

		// Change the view file before executing the CRUD action (so mapActionView works)
		if (array_key_exists($action, $this->_viewMap)) {
			$view = $this->_viewMap[$action];
			$this->_controller->view = $view;
		}

		try {
			if ($models = $this->relatedModels($action)) {
				list($plugin, $class) = pluginSplit($this->_relatedListEventClass, true);
				App::uses($class, $plugin . 'Controller/Event');
				$this->_controller->getEventManager()->attach(new $class($this->_eventPrefix, $models));
			}

			// Execute the default action, inside this component
			$response = call_user_func_array(array($this, '_' . $this->_actionMap[$action] . 'Action'), $args);
			if ($response instanceof CakeResponse) {
				return $response;
			}
		} catch (Exception $e) {
			if (isset($e->response)) {
				return $e->response;
			}
			throw $e;
		}

		// Render the file based on action name
		return $this->_controller->response = $this->_controller->render($view);
	}

	protected function _setModelProperties() {
		$this->_modelName	= $this->_controller->modelClass;
		$this->_model		= $this->_controller->{$this->_modelName};
	}

	/**
	 * Triggers a Crud event by creating a new subject and filling it with $data
	 * if $data is an instance of CrudEventSubject it will be reused as the subject
	 * objec for this event.
	 *
	 * If Event listenrs return a CakeResponse object, the this methid will throw an
	 * exeption and fill a 'response' property on it with a referente to the response
	 * object.
	 *
	 * @throws Exception if any event listener return a CakeResponse object
	 * @return CrudEventSubject
	 **/
	public function trigger($eventName, $data = array()) {
		$subject = $data instanceof CrudEventSubject ? $data : $this->_getSubject($data);
		$event = new CakeEvent($this->_eventPrefix . '.' . $eventName, $subject);
		$this->_eventManager->dispatch($event);

		if ($event->result instanceof CakeResponse) {
			$exception = new Exception();
			$exception->response = $event->result;
			throw $exception;
		}

		$subject->stopped = false;
		if ($event->isStopped()) {
			$subject->stopped = true;
		}

		return $subject;
	}

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

		$pos = array_search($action, $this->_controller->methods);
		if (false === $pos) {
			$this->_controller->methods[] = $action;
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

		$pos = array_search($action, $this->_controller->methods);
		if (false !== $pos) {
			unset($this->_controller->methods[$pos]);
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
			$this->_viewMap = $action + $this->_viewMap;
			return;
		}

		$this->_viewMap[$action] = $view;
	}

	/**
	 * Map action to a internal request type
	 *
	 * @param string $action The Controller action to fake
	 * @param string $type one of the CRUD events (index, add, edit, delete, view)
	 * @param boolean $enable Should the mapping be enabled right away?
	 * @return void
	 */
	public function mapAction($action, $type, $enable = true) {
		$this->_actionMap[$action] = $type;
		if ($enable) {
			$this->enableAction($action);
		}
	}

	/**
	 * Check if a CRUD action has been mapped (aka should be handled by CRUD component)
	 *
	 * @param string|null $action If null, use the current action
	 * @return boolean
	 */
	public function isActionMapped($action = null) {
		if (empty($action)) {
			$action = $this->_action;
		}

		return false !== array_search($action, $this->settings['actions']);
	}

	/**
	* Map a controller action to a Model::find($method)
	*
	* @platform
	* @param string $action
	* @param strign $method
	* @return void
	*/
	public function mapFindMethod($action, $method) {
		$this->_findMethodMap[$action] = $method;
	}

	/**
	 * Attaches an event listener function to the controller for Crud Events
	 *
	 * @param string|array $events Name of the Crud Event you want to attach to controller
	 * @param callback $callback callable method or closure to be executed on event
	 * @return void
	 **/
	public function on($events, $callback) {
		if (!is_array($events)) {
			$events = array($events);
		}

		foreach ($events as $event) {
			if (!strpos($event, '.')) {
				$event = $this->_eventPrefix . '.' . $event;
			}
			$this->_controller->getEventManager()->attach($callback, $event);
		}
	}

	/**
	 * Enables association list fetching for specified actions.
	 *
	 * @param string|array $actions list of action names to enable
	 * @return void
	 */
	public function enableRelatedList($actions) {
		if (!is_array($actions)) {
			$actions = array($actions);
		}

		foreach ($actions as $action) {
			if (empty($this->settings['relatedLists'][$action])) {
				$this->settings['relatedLists'][$action] = true;
			}
		}
	}

	/**
	 * Sets the list of model relationships to be fetched as lists for an action
	 *
	 * @param array|boolean $models list of model association names to be fetch on $action
	 *  if `true`, list of models will be constructud out of associated models of main controller's model
	 * @param stirng $action name of the action to apply this rule to. If left null then
	 *  it will use the current controller action
	 * @return void
	 */
	public function mapRelatedList($models, $action = null) {
		if (empty($action)) {
			$action = $this->_action;
		}

		if (is_string($models)) {
			$models = array($models);
		}

		$this->settings['relatedLists'][$action] = $models;
	}

	/**
	 * Gets the list of associated model lists to be fetched for an action
	 *
	 * @param array $models list of model association names to be fetch on $action
	 * @param stirng $action name of the action
	 * @return array
	 */
	public function relatedModels($action) {
		// If we don't have any related configuration, look up its alias in _actionMap
		if (empty($this->settings['relatedLists'][$action]) && $this->isActionMapped($action)) {
			$action = $this->_actionMap[$action];
		}

		// If current action isn't configured
		if (!isset($this->settings['relatedLists'][$action])) {
			return array();
		}

		// If the action value is true and we got a configured default, inspect it
		if ($this->settings['relatedLists'][$action] === true && isset($this->settings['relatedLists']['default'])) {
			// If default is false, don't fetch any related records
			if (false === $this->settings['relatedLists']['default']) {
				return array();
			}

			// If it's an array, return it
			if (is_array($this->settings['relatedLists']['default'])) {
				return $this->settings['relatedLists']['default'];
			}
		}

		// Use whatever value there may have been set by the user
		if ($this->settings['relatedLists'][$action] !== true) {
			return $this->settings['relatedLists'][$action];
		}

		// Default to everything associated to the current model
		return array_keys($this->_controller->{$this->_controller->modelClass}->getAssociated());
	}

	/**
	 * Sets the class name to be used as an event listener for generating related models' lists
	 * If called with no arguments it will return currently set up class
	 *
	 * @param string $className
	 * @return string class name to be used as event listener
	 */
	public function relatedModelsListener($className = null) {
		if (empty($className)) {
			return $this->_relatedListEventClass;
		}
		return $this->_relatedListEventClass = $className;
	}

	/**
	 * Helper method to get the passed ID to an action
	 *
	 * @platform
	 * @return string
	 */
	public function getIdFromRequest() {
		if (empty($this->_request->params['pass'][0])) {
			return null;
		}
		return $this->_request->params['pass'][0];
	}

	/**
	 * Create a CakeEvent subject with the required properties
	 *
	 * @param array $additional Additional properties for the subject
	 * @return CrudEventSubject
	 */
	protected function _getSubject($additional = array()) {
		$subject				= new CrudEventSubject();
		$subject->crud			= $this;
		$subject->controller	= $this->_controller;
		$subject->model			= $this->_model;
		$subject->modelClass	= $this->_modelName;
		$subject->action		= $this->_action;
		$subject->request		= $this->_request;
		$subject->response		= $this->_controller->response;
		$subject->set($additional);

		return $subject;
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

		if (!empty($this->_findMethodMap[$action])) {
			return $this->_findMethodMap[$action];
		}

		return $default;
	}

	/**
	 * Generic index action
	 *
	 * Triggers the following callbacks
	 *	- Crud.init
	 *	- Crud.beforePaginate
	 *	- Crud.afterPaginate
	 *	- Crud.beforeRender
	 *
	 * @platform
	 * @param string $id
	 * @return void
	 */
	protected function _indexAction() {
		$findMethod = $this->_getFindMethod(null, 'all');
		$subject = $this->trigger('beforePaginate', compact('findMethod'));

		$Paginator = $this->_Collection->load('Paginator');

		// Copy pagination settings from the controller
		if (!empty($this->_controller->paginate)) {
			$Paginator->settings = array_merge($Paginator->settings, $this->_controller->paginate);
		}

		// If pagination settings is using ModelAlias modify that
		if (!empty($Paginator->settings[$this->_modelName])) {
			$Paginator->settings[$this->_modelName][0] = $subject->findMethod;
			$Paginator->settings[$this->_modelName]['findType'] = $subject->findMethod;
		}
		// Or just work directly on the root key
		else {
			$Paginator->settings[0] = $subject->findMethod;
			$Paginator->settings['findType'] = $subject->findMethod;
		}

		// Push the paginator settings back to Controller
		$this->_controller->paginate = $Paginator->settings;

		// Do the pagination
		$items = $this->_controller->paginate($this->_model);

		$subject = $this->trigger('afterPaginate', compact('items'));
		$items = $subject->items;

		$this->_controller->set(compact('items'));
		$this->trigger('beforeRender');
	}

	/**
	 * Generic add action
	 *
	 * Triggers the following callbacks
	 *	- Crud.init
	 *	- Crud.beforeSave
	 *	- Crud.afterSave
	 *	- Crud.beforeRender
	 *
	 * @platform
	 * @param string $id
	 * @return void
	 */
	protected function _addAction() {
		if ($this->_request->is('post')) {
			$this->trigger('beforeSave');
			if ($this->_model->saveAll($this->_request->data, array('validate' => 'first', 'atomic' => true))) {
				$this->_setFlash(sprintf('Succesfully created %s', Inflector::humanize($this->_modelName)), 'success');
				$subject = $this->trigger('afterSave', array('success' => true, 'id' => $this->_model->id));
				$this->_redirect($subject, array('action' => 'index'));
				return false;
			} else {
				$this->_setFlash(sprintf('Could not create %s', Inflector::humanize($this->_modelName)), 'error');
				$this->trigger('afterSave', array('success' => false));
				// Make sure to merge any changed data in the model into the post data
				$this->_request->data = Set::merge($this->_request->data, $this->_model->data);
			}
		}

		$this->trigger('beforeRender', array('success' => false));
	}

	/**
	 * Generic edit action
	 *
	 * Triggers the following callbacks
	 *	- Crud.init
	 *	- Crud.beforeSave
	 *	- Crud.afterSave
	 *	- Crud.beforeFind
	 *	- Crud.recordNotFound
	 *	- Crud.afterFind
	 *	- Crud.beforeRender
	 *
	 * @platform
	 * @param string $id
	 * @return void
	 */
	protected function _editAction($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}
		$this->_validateId($id);

		if ($this->_request->is('put')) {
			$this->trigger('beforeSave', compact('id'));
			if ($this->_model->saveAll($this->_request->data, array('validate' => 'first', 'atomic' => true))) {
				$this->_setFlash(sprintf('%s was succesfully updated', ucfirst(Inflector::humanize($this->_modelName))), 'success');
				$subject = $this->trigger('afterSave', array('id' => $id, 'success' => true));
				$this->_redirect($subject, array('action' => 'index'));
				return false;
			} else {
				$this->_setFlash(sprintf('Could not update %s', Inflector::humanize($this->_modelName)), 'error');
				$this->trigger('afterSave' ,array('id' => $id, 'success' => false));
			}
		} else {
			$query = array();
			$query['conditions'] = array($this->_model->escapeField() => $id);
			$findMethod = $this->_getFindMethod(null, 'first');
			$subject = $this->trigger('beforeFind', compact('query', 'findMethod'));
			$query = $subject->query;

			$this->_request->data = $this->_model->find($subject->findMethod, $query);
			if (empty($this->_request->data)) {
				$subject = $this->trigger('recordNotFound', compact('id'));
				$this->_setFlash(sprintf('Could not find %s', Inflector::humanize($this->_modelName)), 'error');
				$this->_redirect($subject, array('action' => 'index'));
				return false;
			}

			$this->trigger('afterFind', compact('id'));

			// Make sure to merge any changed data in the model into the post data
			$this->_request->data = Set::merge($this->_request->data, $this->_model->data);
		}

		// Trigger a beforeRender
		$this->trigger('beforeRender');
	}

	/**
	 * Generic view action
	 *
	 * Triggers the following callbacks
	 *	- Crud.init
	 *	- Crud.beforeFind
	 *	- Crud.recordNotFound
	 *	- Crud.afterFind
	 *	- Crud.beforeRender
	 *
	 * @platform
	 * @param string $id
	 * @return void
	 */
	protected function _viewAction($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}

		$this->_validateId($id);

		// Build conditions
		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod(null, 'first');
		$subject = $this->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		// Try and find the database record
		$item = $this->_model->find($subject->findMethod, $query);

		// We could not find any record match the conditions in query
		if (empty($item)) {
			$subject = $this->trigger('recordNotFound', compact('id'));
			$this->_setFlash(sprintf('Could not find %s', Inflector::humanize($this->_modelName)), 'error');
			$this->_redirect($subject, array('action' => 'index'));
			return false;
		}

		// We found a record, trigger an afterFind
		$subject = $this->trigger('afterFind', compact('id', 'item'));
		$item = $subject->item;

		// Push it to the view
		$this->_controller->set(compact('item'));

		// Trigger a beforeRender
		$this->trigger('beforeRender', compact('id', 'item'));
	}

	/**
	 * Generic delete action
	 *
	 * Triggers the following callbacks
	 *	- beforeFind
	 *	- recordNotFound
	 *	- beforeDelete
	 *	- afterDelete
	 *
	 * @platform
	 * @param string $id
	 * @return void
	 */
	protected function _deleteAction($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}

		$this->_validateId($id);
		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod(null, 'count');
		$subject = $this->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $this->_model->find($subject->findMethod, $query);
		if (empty($count)) {
			$subject = $this->trigger('recordNotFound', compact('id'));
			$this->_setFlash(sprintf('Could not find %s', Inflector::humanize($this->_modelName)), 'error');
			$this->_redirect($subject, array('action' => 'index'));
			return false;
		}

		$subject = $this->trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->_setFlash(sprintf('Could not delete %s', Inflector::humanize($this->_modelName)), 'error');
			$this->_redirect($subject, array('action' => 'index'));
			return false;
		}

		if ($this->_request->is('delete')) {
			if ($this->_model->delete($id)) {
				$this->_setFlash(sprintf('Successfully deleted %s', Inflector::humanize($this->_modelName)), 'success');
				$subject = $this->trigger('afterDelete', array('id' => $id, 'success' => true));
			} else {
				$this->_setFlash(sprintf('Could not delete %s', Inflector::humanize($this->_modelName)), 'error');
				$subject = $this->trigger('afterDelete', array('id' => $id, 'success' => false));
			}
		} else {
			$this->_setFlash(sprintf('Invalid HTTP request', Inflector::humanize($this->_modelName)), 'error');
		}

		$this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		return false;
	}

	/**
	 * Called for all redirects inside CRUD
	 *
	 * @param array|null $url
	 * @return void
	 */
	protected function _redirect($subject, $url = null) {
		if (!empty($this->_request->data['redirect_url'])) {
			$url = $this->_request->data['redirect_url'];
		} elseif (!empty($this->_request->query['redirect_url'])) {
			$url = $this->_request->query['redirect_url'];
		}

		$subject->url = $url;
		$subject = $this->trigger('beforeRedirect', $subject);
		$url = $subject->url;

		$this->_controller->redirect($url);
	}

	/**
	* Wrapper for Session::setFlash
	*
	* Each param can be modified in setFlash $subject->{$property}
	*
	* @param string $message Message to be flashed
	* @param string $element Element to wrap flash message in.
	* @param array $params Parameters to be sent to layout as view variables
	* @param string $key Message key, default is 'flash'
	* @return void
	*/
	protected function _setFlash($message, $element = 'default', $params = array(), $key = 'flash') {
		$subject = $this->trigger('setFlash', compact('message', 'element', 'params', 'key'));
		$this->Session->setFlash($subject->message, $subject->element, $subject->params, $subject->key);
	}

	/**
	 * Is the passed ID valid ?
	 *
	 * By default we asume you want to validate an UUID string
	 *
	 * Change the validateId settings key to "integer" for is_numeric check instead
	 *
	 * @return boolean
	 */
	protected function _validateId($id, $type = null) {
		if (empty($type)) {
			if (isset($this->settings['validateId'])) {
				$type = $this->settings['validateId'];
			} else {
				$type = 'uuid';
			}
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

		$subject = $this->trigger('invalidId', compact('id'));
		$this->_setFlash('Invalid id', 'error');
		$this->_redirect($subject, $this->_controller->referer());

		return false;
	}
}
