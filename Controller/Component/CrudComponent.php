<?php

App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * Crud component
 *
 * Scaffolding on steroids! :)
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @see http://book.cakephp.org/2.0/en/controllers/components.html#Component
 * @copyright Nodes ApS, 2012
 */
class CrudComponent extends Component {

/**
 * Reference to a Session component
 *
 * @var array
 */
	public $components = array('Session');

/**
 * The current controller action
 *
 * @var string
 */
	protected $_action;

/**
 * Reference to the current controller
 *
 * @var Controller
 */
	protected $_controller;

/**
 * Reference to the current request
 *
 * @var CakeRequest
 */
	protected $_request;

/**
 * Reference to the current event manager
 *
 * @var CakeEventManager
 */
	protected $_eventManager;

/**
 * Cached property for Controller::modelClass
 *
 * @var string
 */
	protected $_modelName;

/**
 * Cached property for the current Controller::modelClass instance
 *
 * @var Model
 */
	protected $_model;

/**
 * List of listener objects attached to Crud
 *
 * @var array
 */
	protected $_listenerInstances = array();

/**
 * List of crud actions
 *
 * @var array
 */
	protected $_actionInstances = array();

/**
 * Components settings.
 *
 * `validateId` ID Argument validation - by default it will inspect your model's primary key
 * and based on it's data type either use integer or uuid validation.
 * Can be disabled by setting it to "false". Supports "integer" and "uuid" configuration
 * By default it's configuration is NULL, which means "auto detect"
 *
 * `eventPrefix` All emitted events will be prefixed with this property value
 *
 * `secureDelete` delete() can only be called with the HTTP DELETE verb, not POST when `true`.
 * If set to `false` HTTP POST is also acceptable
 *
 * `actions` contains an array of controller methods this component should offer implementation for.
 * The actions is used for actionMap, viewMap and findMethodMap to change behavior of CrudComponent
 * By default no actions are enabled
 *
 * `translations` is the settings for the translations Event, responsible for the text used in flash messages
 * see TranslationsEvent::$_defaults the full list of options
 *
 * `relatedList` is a map of the controller action and the whether it should fetch associations lists
 * to be used in select boxes. An array as value means it is enabled and represent the list
 * of model associations to be fetched
 *
 * `actionMap` A map of the controller action and what CRUD action we should call.
 * By default it supports non-prefix and admin_ prefixed routes
 *
 * `listenerClassMap` List of internal-name => ${plugin}.${class} listeners
 * that will be bound automatically in Crud. By default translations and related model events
 * are bound. Events will always assume to be in the Controller/Event folder
 *
 * @var array
 */
	public $settings = array(
		'eventPrefix' => 'Crud',
		'translations' => array(),
		'listenerClassMap' => array(
			'translations' => 'Crud.TranslationsListener',
			'related' => 'Crud.RelatedModelsListener'
		),
		'actions' => array(
			'index' => 'index',
			'add' => 'add',
			'edit' => 'edit',
			'view' => 'view',
			'delete' => 'delete',

			'admin_index' => 'index',
			'admin_add' => 'add',
			'admin_edit' => 'edit',
			'admin_view' => 'view',
			'admin_delete' => 'delete'
		),
		'actionClassMap' => array(
			'index' => 'Crud.Index',
			'add' => 'Crud.Add',
			'edit' => 'Crud.Edit',
			'view' => 'Crud.View',
			'delete' => 'Crud.Delete'
		)
	);

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
 * Make sure to update the list of known controller methods before startup is called
 *
 * The reason for this is that if we don't, the Auth component won't execute any callbacks on the controller
 * like isAuthorized
 *
 * @param Controller $controller
 * @return void
 */
	public function initialize(Controller $controller) {
		if ($controller->name === 'CakeError') {
			return true;
		}

		$this->_controller = $controller;
		$this->_controller->methods = array_keys(array_flip($this->_controller->methods) + array_flip(array_keys($this->settings['actions'])));

		$this->_action = $this->_controller->request->action;
		$this->_request = $this->_controller->request;
		$this->_eventManager = $this->_controller->getEventManager();

		if (!isset($this->_controller->dispatchComponents)) {
			$this->_controller->dispatchComponents = array();
		}

		$name = str_replace('Component', '', get_class($this));
		$this->_controller->dispatchComponents[$name] = true;
	}

/**
 * Execute a Crud action
 *
 * @param string $controllerAction Override the controller action to execute as
 * @param array $arguments List of arguments to pass to the CRUD action (Usually an ID to edit / delete)
 * @return mixed void, or a CakeResponse object
 * @throws RuntimeException If an action is not mapped
 */
	public function executeAction($controllerAction = null, $args = array()) {
		$view = $action = $controllerAction ?: $this->_action;
		$this->_setModelProperties();

		// Make sure to update internal action property
		$this->_action = $action;

		$this->_loadActions();
		$this->_loadListeners();

		// Trigger init callback
		$this->trigger('init');

		try {
			// Execute the default action, inside this component
			$response = $this->trigger('handle', $this->getSubject(compact('args')));
			if ($response instanceof CakeResponse) {
				return $response;
			}
		} catch (Exception $e) {
			if (isset($e->response)) {
				return $e->response;
			}

			throw $e;
		}

		$view = $this->getAction($action)->view();
		return $this->_controller->response = $this->_controller->render($view);
	}

/**
 * Load all event classes attached to Crud
 *
 * @return void
 */
	protected function _loadListeners() {
		foreach (array_keys($this->config('listenerClassMap')) as $name) {
			$this->_loadListener($name);
		}
	}

/**
 * Get a single event class
 *
 * @param string $name
 * @return CrudBaseEvent
 */
	public function getListener($name, $create = true) {
		if (empty($this->_listenerInstances[$name])) {
			$this->_loadListener($name);
		}

		return $this->_listenerInstances[$name];
	}

/**
 * Load a single event class attached to Crud
 *
 * @param string $name
 * @return void
 */
	protected function _loadListener($name) {
		$config = $this->config('listenerClassMap.' . $name);

		list($plugin, $class) = pluginSplit($config, true);
		App::uses($class, $plugin . 'Controller/Crud/Listener');

		// Make sure to cleanup duplicate events
		if (isset($this->_listenerInstances[$name])) {
			$this->_eventManager->detach($this->_listenerInstances[$name]);
			unset($this->_listenerInstances[$name]);
		}

		$subject = $this->getSubject();
		$this->_listenerInstances[$name] = new $class($subject);
		$this->_eventManager->attach($this->_listenerInstances[$name]);
	}

	protected function _loadActions() {
		foreach (array_keys($this->config('actions')) as $name) {
			$this->_loadAction($name);
		}
	}

	protected function _loadAction($name) {
		$actionType = $this->config('actions.' . $name);
		if (empty($actionType)) {
			throw new RuntimeException(sprintf('Action "%s" has not been mapped to any action object', $name));
		}

		$actionClass = $this->config('actionClassMap.' . $actionType);
		if (empty($actionClass)) {
			throw new RuntimeException(sprintf('Action type "%s" for action "%s" has not been mapped', $actionType, $name));
		}

		list($plugin, $class) = pluginSplit($actionClass, true);
		$class .= 'CrudAction';

		App::uses($class, $plugin . 'Controller/Crud/Action');

		// Make sure to cleanup duplicate events
		if (!isset($this->_actionInstances[$name])) {
			$subject = $this->getSubject(array('handleAction' => $name));
			$this->_actionInstances[$name] = new $class($subject);
			$this->_eventManager->attach($this->_actionInstances[$name]);
		}

		return $this->_actionInstances[$name];
	}

	public function getAction($name = null) {
		if (empty($name)) {
			$name = $this->_action;
		}

		return $this->_loadAction($name);
	}

/**
 * Set internal model properties from the controller
 *
 * @return void
 * @throws RuntimeException If unable to get model instance
 */
	protected function _setModelProperties() {
		$configKey = 'modelMap.' . $this->_action;
		if (!$this->_modelName = $this->config($configKey)) {
			$this->_modelName = $this->_controller->modelClass;
		}

		$this->_model = $this->_controller->{$this->_modelName};
		if (empty($this->_model)) {
			throw new RuntimeException('No model loaded in the Controller by the name "' . $this->_modelName . '". Please add it to $uses.');
		}
	}

/**
 * Triggers a Crud event by creating a new subject and filling it with $data
 * if $data is an instance of CrudSubject it will be reused as the subject
 * object for this event.
 *
 * If Event listeners return a CakeResponse object, the this method will throw an
 * exception and fill a 'response' property on it with a reference to the response
 * object.
 *
 * @param string $eventName
 * @param array $data
 * @throws Exception if any event listener return a CakeResponse object
 * @return CrudSubject
 */
	public function trigger($eventName, $data = array()) {
		$subject = $data instanceof CrudSubject ? $data : $this->getSubject($data);
		$event = new CakeEvent($this->settings['eventPrefix'] . '.' . $eventName, $subject);
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
 * @param string $action The action to enable
 * @return void
 */
	public function enableAction($action) {
		$this->getAction($action)->enable($action);
	}

/**
 * Disable a CRUD action
 *
 * @param string $action The action to disable
 * @return void
 */
	public function disableAction($action) {
		$this->getAction($action)->disable($action);
	}

/**
 * Map the view file to use for a controller action
 *
 * To map multiple action views in one go pass an array as first argument and no second argument
 *
 * @param string|array $action
 * @param string $view
 * @return void
 */
	public function mapActionView($action, $view = null) {
		if (is_array($action)) {
			foreach ($action as $realAction => $realView) {
				$this->getAction($realAction)->view($realView);
			}
			return;
		}

		$this->getAction($action)->view($view);
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
		$this->config('actions.' . $action, $type);

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

		try {
			return $this->getAction($action)->config('enabled', null, $action);
		} catch (Exception $e) {
			return false;
		}
	}

/**
 * Map a controller action to a Model::find($method)
 *
 * @param string $action
 * @param strign $method
 * @return void
 */
	public function mapFindMethod($action, $method = null) {
		$this->getAction($action)->findMethod($method);
	}

/**
 * Attaches an event listener function to the controller for Crud Events
 *
 * @param string|array $events Name of the Crud Event you want to attach to controller
 * @param callback $callback callable method or closure to be executed on event
 * @return void
 */
	public function on($events, $callback) {
		if (!is_array($events)) {
			$events = array($events);
		}

		foreach ($events as $event) {
			if (!strpos($event, '.')) {
				$event = $this->settings['eventPrefix'] . '.' . $event;
			}

			$this->_eventManager->attach($callback, $event);
		}
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
 * @return mixed
 */
	public function config($key = null, $value = null) {
		// Get all settings
		if (is_null($key) && is_null($value)) {
			return $this->settings;
		}

		if (is_null($value)) {
			if (is_array($key)) {
				$this->settings = $this->settings + (array)$key;
				return;
			}

			return Hash::get($this->settings, $key);
		}

		if (is_array($value)) {
			$value = $value + (array)Hash::get($this->settings, $key);
		}

		$this->settings = Hash::insert($this->settings, $key, $value);
	}

/**
 * Create a CakeEvent subject with the required properties
 *
 * @param array $additional Additional properties for the subject
 * @return CrudSubject
 */
	public function getSubject($additional = array()) {
		if (empty($this->_model) || empty($this->_modelName)) {
			$this->_setModelProperties();
		}

		$subject = new CrudSubject();
		$subject->crud = $this;
		$subject->controller = $this->_controller;
		$subject->collection = $this->_Collection;
		$subject->model = $this->_model;
		$subject->modelClass = $this->_modelName;
		$subject->action = $this->_action;
		$subject->request = $this->_request;
		$subject->response = $this->_controller->response;
		$subject->set($additional);

		return $subject;
	}

}
