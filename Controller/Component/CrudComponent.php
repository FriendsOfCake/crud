<?php

App::uses('Component', 'Controller');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * Crud component
 *
 * Scaffolding on steroids! :)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
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
 * `eventPrefix` All emitted events will be prefixed with this property value
 *
 * `actions` contains an array of controller methods this component should offer implementation for.
 * Each action maps to a CrudAction class. `$controllerAction => $crudActionClass`.
 * Example: `array('admin_index' => 'Crud.Index')`
 * By default no actions are enabled
 *
 * `listeners` List of internal-name => ${plugin}.${class} listeners
 * that will be bound automatically in Crud. By default translations and related model events
 * are bound. Events will always assume to be in the Controller/Event folder
 *
 * @var array
 */
	public $settings = array(
		'eventPrefix' => 'Crud',
		'actions' => array(),
		'listeners' => array(
			'translations' => 'Crud.TranslationsListener',
			'related' => 'Crud.RelatedModelsListener'
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

		$this->_normalizeActionConfiguration();

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
		$this->_action = $action;
		$this->_loadListeners();
		$this->trigger('init');

		try {
			// Execute the default action, inside this component
			$response = $this->getAction($action)->handle($this->getSubject(compact('args')));
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
 * Get an CrudAction object by action name
 *
 * @param string $name The controller action name
 * @return CrudAction
 */
	public function getAction($name = null) {
		if (empty($name)) {
			$name = $this->_action;
		}

		return $this->_loadAction($name);
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
			return $this->getAction($action)->config('enabled');
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
 * Get a single event class
 *
 * @param string $name
 * @return CrudBaseEvent
 */
	public function getListener($name) {
		return $this->_loadListener($name);
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
 * Set or get defaults for listeners and actions
 *
 * @param string $type Can be anything, but 'listener' or 'action' is currently only used
 * @param mixed $name The name of the $type - e.g. 'api', 'translations', 'related'
 * 	or an array ('api', 'translations'). If $name is an array, the $config will be applied
 * 	to each entry in the $name array.
 * @param mixed $config If NULL, the defaults is returned, else the defaults are changed
 * @return mixed
 */
	public function defaults($type, $name, $config = null) {
		if (!is_null($config)) {
			if (!is_array($name)) {
				$name = array($name);
			}

			foreach ($name as $realName) {
				$this->config(sprintf('defaults.%s.%s', $type, $realName), $config);
			}

			return;
		}

		return $this->config(sprintf('defaults.%s.%s', $type, $name));
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
 * Normalize action configuration
 *
 * If an action don't have a CrudClass specified (the value part of the array)
 * try to compute it by exploding on action name on '_' and take the last chunk
 * as CrudClass identifier
 *
 * @return void
 */
	protected function _normalizeActionConfiguration() {
		$this->settings['actions'] = Hash::normalize($this->settings['actions']);
		foreach ($this->settings['actions'] as $action => $class) {
			if (!empty($class)) {
				continue;
			}

			if (false !== strstr($action, '_')) {
				list($prefix, $class) = explode('_', $action, 2);
			} else {
				$class = $action;
			}

			$this->settings['actions'][$action] = 'Crud.' . ucfirst($class);
		}
	}

/**
 * Load all event classes attached to Crud
 *
 * @return void
 */
	protected function _loadListeners() {
		foreach (array_keys($this->config('listeners')) as $name) {
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
		if (!isset($this->_listenerInstances[$name])) {
			$config = $this->config('listeners.' . $name);

			list($plugin, $class) = pluginSplit($config, true);
			App::uses($class, $plugin . 'Controller/Crud/Listener');

			$subject = $this->getSubject();
			$this->_listenerInstances[$name] = new $class($subject, $this->defaults('listener', $name));
			$this->_eventManager->attach($this->_listenerInstances[$name]);
		}

		return $this->_listenerInstances[$name];
	}

/**
 * Load a CrudAction instance
 *
 * @param string $name The controller action name
 * @return CrudAction
 */
	protected function _loadAction($name) {
		$actionClass = $this->config('actions.' . $name);
		if (empty($actionClass)) {
			throw new RuntimeException(sprintf('Action "%s" has not been mapped', $name));
		}

		if (!isset($this->_actionInstances[$name])) {
			list($plugin, $class) = pluginSplit($actionClass, true);
			$class .= 'CrudAction';
			$class = ucfirst($class);

			if (empty($plugin)) {
				$plugin = 'Crud.';
			}

			App::uses($class, $plugin . 'Controller/Crud/Action');

			$subject = $this->getSubject(array('handleAction' => $name));
			$this->_actionInstances[$name] = new $class($subject, $this->defaults('action', $name));
			$this->_eventManager->attach($this->_actionInstances[$name]);
		}

		return $this->_actionInstances[$name];
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

}
