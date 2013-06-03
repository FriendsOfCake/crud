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
	protected $_listeners = array();

/**
 * List of crud actions
 *
 * @var array
 */
	protected $_actions = array();

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
 * `saveAllOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * `actionMap` A map of the controller action and what CRUD action we should call.
 * By default it supports non-prefix and admin_ prefixed routes
 *
 * `viewMap` A map of the controller action and the view to render
 * By default it supports non-prefix and admin_ prefixed routes
 *
 * `findMethodMap` The default find method for reading data
 *
 * `listenerClassMap` List of internal-name => ${plugin}.${class} listeners
 * that will be bound automatically in Crud. By default translations and related model events
 * are bound. Events will always assume to be in the Controller/Event folder
 *
 * @var array
 */
	public $settings = array(
		'validateId' => null,
		'secureDelete' => false,
		'eventPrefix' => 'Crud',
		'actions' => array(),
		'translations' => array(),
		'relatedLists' => array(
			'add' => true,
			'edit' => true,

			'admin_add' => true,
			'admin_edit' => true
		),
		'saveAllOptions' => array(
			'default' => array(
				'validate' => 'first',
				'atomic' => true
			)
		),
		'actionMap' => array(
			'index'	=> 'index',
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
		'viewMap' => array(
			'index' => 'index',
			'add' => 'add',
			'edit' => 'edit',
			'view' => 'view',

			'admin_index' => 'admin_index',
			'admin_add' => 'admin_add',
			'admin_edit' => 'admin_edit',
			'admin_view' => 'admin_view'
		),
		'findMethodMap' => array(
			'index'	=> 'all',
			'edit' => 'first',
			'view' => 'first',
			'delete' => 'count',

			'admin_index' => 'all',
			'admin_edit' => 'first',
			'admin_view' => 'first',
			'admin_delete' => 'count'
		),
		'listenerClassMap' => array(
			'translations' => 'Crud.TranslationsListener',
			'related' => 'Crud.RelatedModelsListener'
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
		$this->_controller->methods = array_keys(array_flip($this->_controller->methods) + array_flip($this->settings['actions']));

		// Create some easy accessible class properties
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

		// Test if action is mapped
		$actionMapKey = sprintf('actionMap.%s', $action);
		if (!$this->config($actionMapKey)) {
			throw new RuntimeException(sprintf('Action "%s" has not been mapped', $action));
		}

		// Change the view file before executing the CRUD action (so mapActionView works)
		$viewMapKey = sprintf('viewMap.%s', $action);
		$viewFile = $this->config($viewMapKey);
		if (!empty($viewFile)) {
			$view = $viewFile;
			$this->_controller->view = $viewFile;
		}

		try {
			$actionToInvoke = $this->config($actionMapKey);
			// Execute the default action, inside this component
			$response = $this->trigger('handle', $this->getSubject(array('action' => $actionToInvoke)));
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
 * Load a single event class attached to Crud
 *
 * @param string $name
 * @return void
 */
	protected function _loadListener($name) {
		$subject = $this->getSubject();

		$config = $this->config(sprintf('listenerClassMap.%s', $name));

		list($plugin, $class) = pluginSplit($config, true);
		App::uses($class, $plugin . 'Controller/Crud/Listener');

		// Make sure to cleanup duplicate events
		if (isset($this->_listeners[$name])) {
			$this->_eventManager->detach($this->_listeners[$name]);
			unset($this->_listeners[$name]);
		}

		$this->_listeners[$name] = new $class($subject);
		$this->_eventManager->attach($this->_listeners[$name]);
	}

	protected function _loadActions() {
		foreach (array_keys($this->config('actionClassMap')) as $name) {
			$this->_loadAction($name);
		}
	}

	protected function _loadAction($name) {
		$subject = $this->getSubject();

		$config = $this->config(sprintf('actionClassMap.%s', $name));

		list($plugin, $class) = pluginSplit($config, true);
		$class .= 'CrudAction';

		App::uses($class, $plugin . 'Controller/Crud/Action');

		// Make sure to cleanup duplicate events
		if (isset($this->_actions[$name])) {
			$this->_eventManager->detach($this->_actions[$name]);
			unset($this->_actions[$name]);
		}

		$this->_actions[$name] = new $class($subject);
		$this->_eventManager->attach($this->_actions[$name]);
	}

/**
 * Get a single event class
 *
 * @param string $name
 * @param boolean $created create if it doesn't exist
 * @return CrudBaseEvent
 */
	public function getListener($name, $create = true) {
		if (empty($this->_listeners[$name])) {
			if (!$create) {
				return false;
			}

			$this->_loadListener($name);
		}

		return $this->_listeners[$name];
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
		$event = new CakeEvent($this->config('eventPrefix') . '.' . $eventName, $subject);
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
 * @param string|array $action
 * @param string $view
 * @return void
 */
	public function mapActionView($action, $view = null) {
		if (is_array($action)) {
			$this->config('viewMap', $action);
			return;
		}

		$this->config(sprintf('viewMap.%s', $action), $view);
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
		$this->config(sprintf('actionMap.%s', $action), $type);
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
 * @param string $action
 * @param strign $method
 * @return void
 */
	public function mapFindMethod($action, $method) {
		$this->config(sprintf('findMethodMap.%s', $action), $method);
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
				$event = $this->config('eventPrefix') . '.' . $event;
			}
			$this->_controller->getEventManager()->attach($callback, $event);
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
 * @return TranslationsEvent
 */
	public function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return $this->settings;
		}

		if (is_null($value)) {
			if (is_array($key)) {
				$this->settings = $this->settings + $key;
				return $this;
			}

			return Hash::get($this->settings, $key);
		}

		if (is_array($value)) {
			$value = $value + (array)Hash::get($this->settings, $key);
		}

		$this->settings = Hash::insert($this->settings, $key, $value);
		return $this;
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

/**
 * Wrapper for Session::setFlash
 *
 * @param string $type Message type
 * @return void
 */
	public function setFlash($type) {
		$name = $this->_getResourceName();
		$this->getListener('translations');

		// default values
		$message = $element = $key = null;
		$params = array();

		$subject = $this->trigger('setFlash', compact('message', 'element', 'params', 'key', 'type', 'name'));
		$this->Session->setFlash($subject->message, $subject->element, $subject->params, $subject->key);
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
