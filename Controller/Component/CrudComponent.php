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
 * that will be bound automatically in Crud. By default the related model event
 * are bound. Events will always assume to be in the Controller/Event folder
 *
 * @var array
 */
	public $settings = array(
		'actions' => array(),
		'eventPrefix' => 'Crud',
		'listeners' => array(
			'RelatedModels' => 'Crud.RelatedModels'
		),
		'messages' => array(
			'domain' => 'crud',
			'invalidId' => array(
				'code' => 400,
				'class' => 'BadRequestException',
				'text' => 'Invalid id'
			),
			'recordNotFound' => array(
				'code' => 404,
				'class' => 'NotFoundException',
				'text' => 'Not found'
			),
			'badRequestMethod' => array(
				'code' => 405,
				'class' => 'MethodNotAllowedException',
				'text' => 'Method not allowed. This action permits only {methods}'
			)
		)
	);

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
 * @param array $settings Array of configuration settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $this->_mergeConfig($this->settings, $settings));
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
		$this->_normalizeConfig();

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
 * initAction
 *
 * @param string $controllerAction Override the controller action to execute as
 * @param mixed $controllerAction
 */
	public function initAction($controllerAction = null) {
		$this->_action = $controllerAction ?: $this->_action;

		$this->_setModelProperties();
		$this->_loadListeners();
		$this->trigger('init');
	}

/**
 * Execute a Crud action
 *
 * @param string $controllerAction Override the controller action to execute as
 * @param array $arguments List of arguments to pass to the CRUD action (Usually an ID to edit / delete)
 * @return mixed void, or a CakeResponse object
 * @throws CakeException If an action is not mapped
 */
	public function executeAction($controllerAction = null, $args = array()) {
		$this->initAction($controllerAction);
		$view = $action = $this->_action;

		try {
			// Execute the default action, inside this component
			$response = $this->action($action)->handle($this->getSubject(compact('args')));

			if ($response instanceof CakeResponse) {
				return $response;
			}
		} catch (Exception $e) {
			if (isset($e->response)) {
				return $e->response;
			}

			throw $e;
		}

		$view = $this->action($action)->view();
		return $this->_controller->response = $this->_controller->render($view);
	}

/**
 * Get an CrudAction object by action name
 *
 * @param string $name The controller action name
 * @return CrudAction
 */
	public function action($name = null) {
		if (empty($name)) {
			$name = $this->_action;
		}

		return $this->_loadAction($name);
	}

/**
 * Enable one or multiple CRUD actions
 *
 * @param string $actions The action to enable
 * @return void
 */
	public function enable($actions) {
		if (!is_array($actions)) {
			$actions = array($actions);
		}

		foreach ($actions as $action) {
			$this->action($action)->enable();
		}
	}

/**
 * Disable one or multiple CRUD actions
 *
 * @param string $actions The action to disable
 * @return void
 */
	public function disable($actions) {
		if (!is_array($actions)) {
			$actions = array($actions);
		}

		foreach ($actions as $action) {
			$this->action($action)->disable();
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
	public function view($action, $view = null) {
		if (is_array($action)) {
			foreach ($action as $realAction => $realView) {
				$this->action($realAction)->view($realView);
			}

			return;
		}

		$this->action($action)->view($view);
	}

/**
 * Change the viewVar name for one or multiple actions
 *
 * To map multiple action viewVars in one go pass an array as first argument and no second argument
 *
 * @param string|array $action
 * @param string $viewVar
 * @return void
 */
	public function viewVar($action, $viewVar = null) {
		if (is_array($action)) {
			foreach ($action as $realAction => $realViewVar) {
				$this->action($realAction)->viewVar($realViewVar);
			}

			return;
		}

		$this->action($action)->viewVar($viewVar);
	}

/**
 * Map a controller action to a Model::find($method)
 *
 * To map multiple findMethods in one go pass an array as first argument and no second argument
 *
 * @param string $action
 * @param strign $method
 * @return void
 */
	public function findMethod($action, $method = null) {
		if (is_array($action)) {
			foreach ($action as $realAction => $realMethod) {
				$this->action($realAction)->findMethod($realMethod);
			}

			return;
		}

		$this->action($action)->findMethod($method);
	}

/**
 * Map action to a internal request type
 *
 * @param string $action The Controller action to provide an implementation for
 * @param string|array $setting Settings array or one of the CRUD events (index, add, edit, delete, view)
 * @param boolean $enable Should the mapping be enabled right away?
 * @return void
 */
	public function mapAction($action, $settings, $enable = true) {
		$this->config('actions.' . $action, $settings);
		$this->_normalizeConfig('actions');

		if ($enable) {
			$this->enable($action);
		}
	}

/**
 * Check if a CRUD action has been mapped (whether it will be handled by CRUD component)
 *
 * @param string|null $action If null, use the current action
 * @return boolean
 */
	public function isActionMapped($action = null) {
		if (empty($action)) {
			$action = $this->_action;
		}

		try {
			return $this->action($action)->config('enabled');
		} catch (Exception $e) {

		}

		return false;
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
	public function listener($name) {
		return $this->_loadListener($name);
	}

/**
 * Add a new listener to Crud
 *
 * This will not load or initialize the listener, only lazy-load it
 *
 * If `$name` is provided but no `$class` argument, the className will
 * be derived from the `$name`.
 *
 * CakePHP Plugin.ClassName format for `$name` and `$class` is supported
 *
 * @param string $name
 * @param string|null $class Normal CakePHP plugin-dot annotation supported
 * @param array $defaults Any default settings for a listener
 * @return void
 */
	public function addListener($name, $class = null, $defaults = array()) {
		if (false !== strpos($name, '.')) {
			list($plugin, $name) = pluginSplit($name);
			$name = strtolower($name);
			$class = $plugin . '.' . ucfirst($name);
		}

		$this->config(sprintf('listeners.%s', $name), array('className' => $class) + $defaults);
	}

/**
 * Remove a listener from Crud
 *
 * This will also detach it from the EventManager if it's attached
 *
 * @param string $name
 * @return boolean
 */
	public function removeListener($name) {
		$listeners = $this->config('listeners');
		if (!array_key_exists($name, $listeners)) {
			return false;
		}

		if (isset($this->_listenerInstances[$name])) {
			$this->_eventManager->detach($this->_listenerInstances[$name]);
			unset($this->_listenerInstances[$name]);
		}

		unset($listeners[$name]);
		$this->settings['listeners'] = $listeners;
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
 * Sets a configuration variable into this component
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
 * @return mixed|CrudComponent
 */
	public function config($key = null, $value = null) {
		if (is_null($key) && is_null($value)) {
			return $this->settings;
		}

		if (is_null($value)) {
			if (is_array($key)) {
				$this->settings = $key + $this->settings;
				return $this;
			}

			return Hash::get($this->settings, $key);
		}

		if (is_array($value)) {
			$value = array_merge((array)Hash::get($this->settings, $key), $value);
		}

		$this->settings = Hash::insert($this->settings, $key, $value);
		foreach (array('listeners', 'actions') as $type) {
			if (strpos($key, $type . '.') === 0) {
				$this->_normalizeConfig($type);
			}
		}

		return $this;
	}

/**
 * Set or get defaults for listeners and actions
 *
 * @param string $type Can be anything, but 'listeners' or 'actions' is currently only used
 * @param mixed $name The name of the $type - e.g. 'api', 'relatedModels'
 * 	or an array ('api', 'relatedModels'). If $name is an array, the $config will be applied
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
				$this->config(sprintf('%s.%s', $type, $realName), $config);
			}

			return;
		}

		return $this->config(sprintf('%s.%s', $type, $name));
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
 * @param mixed $types Class type(s)
 * @return void
 * @throws CakeException If className is missing for listener
 */
	protected function _normalizeConfig($types = null) {
		if (!$types) {
			$types = array('listeners', 'actions');
		}

		foreach ((array)$types as $type) {
			$this->settings[$type] = Hash::normalize($this->settings[$type]);

			foreach ($this->settings[$type] as $name => $settings) {
				if (is_array($settings) && !empty($settings['className'])) {
					$this->settings[$type][$name] = $settings;
					continue;
				}

				$className = null;
				if (empty($settings)) {
					$settings = array();
				} elseif (is_string($settings)) {
					$className = $settings;
					$settings = array();
				}

				if ($type === 'listeners' && false !== strpos($name, '.')) {
					unset($this->settings[$type][$name]);

					list($plugin, $name) = pluginSplit($name);
					$name = Inflector::camelize($name);
				}

				$className = $this->_handlerClassName($name, $className);
				$settings['className'] = $className;
				$this->settings[$type][$name] = $settings;
			}
		}
	}

/**
 * Generate valid class name for action and listener handler
 *
 * @param string $action
 * @param string|null $className
 * @return string Class name
 */
	protected function _handlerClassName($action, $className) {
		if (empty($className)) {
			if (false !== strstr($action, '_')) {
				list($prefix, $className) = explode('_', $action, 2);
				$className = 'Crud.' . ucfirst($className);
			} else {
				$className = 'Crud.' . ucfirst($action);
			}
		} elseif (false === strpos($className, '.')) {
			$className = 'Crud.' . ucfirst($className);
		}

		return ucfirst($className);
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
 * @throws CakeException
 * @param string $name
 * @return void
 */
	protected function _loadListener($name) {
		if (!isset($this->_listenerInstances[$name])) {
			$config = $this->config('listeners.' . $name);

			if (empty($config)) {
				throw new CakeException(sprintf('Listener "%s" is not configured', $name));
			}

			list($plugin, $class) = pluginSplit($config['className'], true);
			$class .= 'Listener';
			App::uses($class, $plugin . 'Controller/Crud/Listener');

			$subject = $this->getSubject();
			$this->_listenerInstances[$name] = new $class($subject, $config);
			$this->_eventManager->attach($this->_listenerInstances[$name]);
		}

		return $this->_listenerInstances[$name];
	}

/**
 * Load a CrudAction instance
 *
 * @param string $name The controller action name
 * @return CrudAction
 * @throws CakeException If action is not mapped
 */
	protected function _loadAction($name) {
		if (!isset($this->_actionInstances[$name])) {
			$config = $this->config('actions.' . $name);

			if (empty($config)) {
				throw new CakeException(sprintf('Action "%s" has not been mapped', $name));
			}

			list($plugin, $class) = pluginSplit($config['className'], true);
			$class = ucfirst($class);

			if (in_array($class, array('Index', 'View', 'Add', 'Edit', 'Delete'))) {
				if (!empty($plugin) && $plugin !== 'Crud.') {
					throw new CakeException('The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin');
				}

				$plugin = 'Crud.';
			}

			$class .= 'CrudAction';
			App::uses($class, $plugin . 'Controller/Crud/Action');
			$subject = $this->getSubject(array('action' => $name));
			$this->_actionInstances[$name] = new $class($subject, $config);
			$this->_eventManager->attach($this->_actionInstances[$name]);
		}

		return $this->_actionInstances[$name];
	}

/**
 * Set internal model properties from the controller
 *
 * @return void
 * @throws CakeException If unable to get model instance
 */
	protected function _setModelProperties() {
		$configKey = 'modelMap.' . $this->_action;
		if (!$this->_modelName = $this->config($configKey)) {
			$this->_modelName = $this->_controller->modelClass;
		}

		$this->_model = $this->_controller->{$this->_modelName};
		if (empty($this->_model)) {
			throw new CakeException('No model loaded in the Controller by the name "' . $this->_modelName . '". Please add it to $uses.');
		}
	}

/**
 * Merge configuration arrays
 *
 * Allow us to change e.g. a listener config without losing defaults
 *
 * This is like merge_array_recursive - with the difference that
 * duplicate keys isn't changed to an array with both values, but
 * overridden
 *
 * @param array $array1
 * @param array $array2
 * @return array
 */
	protected function _mergeConfig(array $array1, array $array2) {
		$merged = $array1;
		foreach ($array2 as $key => $value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = $this->_mergeConfig($merged[$key], $value);
				continue;
			}

			$merged[$key] = $value;
		}

		return $merged;
	}

}
