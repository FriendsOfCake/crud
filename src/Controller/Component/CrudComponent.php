<?php
namespace Crud\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Utility\Inflector;
use Crud\Error\Exception\ActionNotConfiguredException;
use Crud\Error\Exception\ListenerNotConfiguredException;
use Crud\Error\Exception\MissingActionException;
use Crud\Error\Exception\MissingListenerException;
use Crud\Event\Subject;
use Exception;

/**
 * Crud component
 *
 * Scaffolding on steroids! :)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class CrudComponent extends Component
{

    /**
     * The current controller action.
     *
     * @var string
     */
    protected $_action;

    /**
     * Reference to the current controller.
     *
     * @var \Cake\Controller\Controller
     */
    protected $_controller;

    /**
     * Reference to the current request.
     *
     * @var \Cake\Http\ServerRequest
     */
    protected $_request;

    /**
     * A flat array of the events triggered.
     *
     * @var array
     */
    protected $_eventLog = [];

    /**
     * Reference to the current event manager.
     *
     * @var \Cake\Event\EventManager
     */
    protected $_eventManager;

    /**
     * Cached property for Controller::$modelClass. This is
     * the model name of the current model.
     *
     * @var string
     */
    protected $_modelName;

    /**
     * List of listener objects attached to Crud.
     *
     * @var array
     */
    protected $_listenerInstances = [];

    /**
     * List of crud actions.
     *
     * @var array
     */
    protected $_actionInstances = [];

    /**
     * Components settings.
     *
     * `eventPrefix` All emitted events will be prefixed with this property value.
     *
     * `actions` contains an array of controller methods this component should offer implementation for.
     * Each action maps to a CrudAction class. `$controllerAction => $crudActionClass`.
     * Example: `array('admin_index' => 'Crud.Index')`
     * By default no actions are enabled.
     *
     * `listeners` List of internal-name => ${plugin}.${class} listeners
     * that will be bound automatically in Crud. By default the related model event
     * are bound. Events will always assume to be in the Controller/Event folder.
     *
     * `eventLogging` boolean to determine whether the class should log triggered events.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => [],
        'eventPrefix' => 'Crud',
        'listeners' => [],
        'messages' => [
            'domain' => 'crud',
            'invalidId' => [
                'code' => 400,
                'class' => BadRequestException::class,
                'text' => 'Invalid id'
            ],
            'recordNotFound' => [
                'code' => 404,
                'class' => NotFoundException::class,
                'text' => 'Not found'
            ],
            'badRequestMethod' => [
                'code' => 405,
                'class' => MethodNotAllowedException::class,
                'text' => 'Method not allowed. This action permits only {methods}'
            ]
        ],
        'eventLogging' => false
    ];

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $collection A ComponentCollection this component
     *   can use to lazy load its components.
     * @param array $config Array of configuration settings.
     */
    public function __construct(ComponentRegistry $collection, $config = [])
    {
        $config += ['actions' => [], 'listeners' => []];
        $config['actions'] = $this->normalizeArray($config['actions']);
        $config['listeners'] = $this->normalizeArray($config['listeners']);

        $this->_controller = $collection->getController();
        $this->_eventManager = $this->_controller->getEventManager();

        parent::__construct($collection, $config);
    }

    /**
     * Normalize config array
     *
     * @param array $array List to normalize
     * @return array
     */
    public function normalizeArray(array $array)
    {
        $normal = [];

        foreach ($array as $action => $config) {
            if (is_string($config)) {
                $config = ['className' => $config];
            }

            if (is_int($action)) {
                list(, $action) = pluginSplit($config['className']);
            }

            $action = Inflector::variable($action);
            $normal[$action] = $config;
        }

        return $normal;
    }

    /**
     * Add self to list of components capable of dispatching an action.
     *
     * @param array $config Configuration values for component.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_action = $this->_controller->request->getParam('action');
        $this->_request = $this->_controller->request;

        if (!isset($this->_controller->dispatchComponents)) {
            $this->_controller->dispatchComponents = [];
        }

        $this->_controller->dispatchComponents['Crud'] = true;
    }

    /**
     * Loads listeners
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     * @throws \Exception
     */
    public function beforeFilter(Event $event)
    {
        $this->_loadListeners();
        $this->trigger('beforeFilter');
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     * @throws \Exception
     */
    public function startup(Event $event)
    {
        $this->_loadListeners();
        $this->trigger('startup');
    }

    /**
     * Execute a Crud action
     *
     * @param string $controllerAction Override the controller action to execute as.
     * @param array $args List of arguments to pass to the CRUD action (Usually an ID to edit / delete).
     * @return \Cake\Http\Response
     * @throws Exception If an action is not mapped.
     */
    public function execute($controllerAction = null, $args = [])
    {
        $this->_loadListeners();

        $this->_action = $controllerAction ?: $this->_action;

        $action = $this->_action;
        if (empty($args)) {
            $args = $this->_request->getParam('pass');
        }

        try {
            $event = $this->trigger('beforeHandle', $this->getSubject(compact('args', 'action')));

            $response = $this->action($event->getSubject()->action)->handle($event->getSubject()->args);
            if ($response instanceof Response) {
                return $response;
            }
        } catch (Exception $e) {
            if (isset($e->response)) {
                return $e->response;
            }

            throw $e;
        }

        $view = null;
        $crudAction = $this->action($action);
        if (method_exists($crudAction, 'view')) {
            $view = $crudAction->view();
        }

        return $this->_controller->response = $this->_controller->render($view);
    }

    /**
     * Get a CrudAction object by action name.
     *
     * @param string|null $name The controller action name.
     * @return \Crud\Action\BaseAction
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function action($name = null)
    {
        if (empty($name)) {
            $name = $this->_action;
        }

        $name = Inflector::variable($name);

        return $this->_loadAction($name);
    }

    /**
     * Enable one or multiple CRUD actions.
     *
     * @param string|array $actions The action to enable.
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function enable($actions)
    {
        foreach ((array)$actions as $action) {
            $this->action($action)->enable();
        }
    }

    /**
     * Disable one or multiple CRUD actions.
     *
     * @param string|array $actions The action to disable.
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function disable($actions)
    {
        foreach ((array)$actions as $action) {
            $this->action($action)->disable();
        }
    }

    /**
     * Map the view file to use for a controller action.
     *
     * To map multiple action views in one go pass an array as first argument and no second argument.
     *
     * @param string|array $action Action or array of actions
     * @param string|null $view View name
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function view($action, $view = null)
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realView) {
                $this->action($realAction)->view($realView);
            }

            return;
        }

        $this->action($action)->view($view);
    }

    /**
     * Change the viewVar name for one or multiple actions.
     *
     * To map multiple action viewVars in one go pass an array as first argument and no second argument.
     *
     * @param string|array $action Action or array of actions.
     * @param string|null $viewVar View var name.
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function viewVar($action, $viewVar = null)
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realViewVar) {
                $this->action($realAction)->viewVar($realViewVar);
            }

            return;
        }

        $this->action($action)->viewVar($viewVar);
    }

    /**
     * Map a controller action to a Model::find($method).
     *
     * To map multiple findMethods in one go pass an array as first argument and no second argument.
     *
     * @param string|array $action Action or array of actions.
     * @param string|null $method Find method name
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function findMethod($action, $method = null)
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realMethod) {
                $this->action($realAction)->findMethod($realMethod);
            }

            return;
        }

        $this->action($action)->findMethod($method);
    }

    /**
     * Map action to an internal request type.
     *
     * @param string $action The Controller action to provide an implementation for.
     * @param string|array $config Config array or class name like Crud.Index.
     * @param bool $enable Should the mapping be enabled right away?
     * @return void
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function mapAction($action, $config = [], $enable = true)
    {
        if (is_string($config)) {
            $config = ['className' => $config];
        }
        $action = Inflector::variable($action);
        $this->setConfig('actions.' . $action, $config);

        if ($enable) {
            $this->enable($action);
        }
    }

    /**
     * Check if a CRUD action has been mapped (whether it will be handled by CRUD component)
     *
     * @param string|null $action If null, use the current action.
     * @return bool
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function isActionMapped($action = null)
    {
        if (empty($action)) {
            $action = $this->_action;
        }

        $action = Inflector::variable($action);
        $test = $this->getConfig('actions.' . $action);
        if (empty($test)) {
            return false;
        }

        return $this->action($action)->getConfig('enabled');
    }

    /**
     * Attaches an event listener function to the controller for Crud Events.
     *
     * @param string|array $events Name of the Crud Event you want to attach to controller.
     * @param callable $callback Callable method or closure to be executed on event.
     * @param array $options Used to set the `priority` and `passParams` flags to the listener.
     * @return void
     */
    public function on($events, $callback, $options = [])
    {
        foreach ((array)$events as $event) {
            if (!strpos($event, '.')) {
                $event = $this->_config['eventPrefix'] . '.' . $event;
            }

            $this->_eventManager->on($event, $options, $callback);
        }
    }

    /**
     * Get a single event class.
     *
     * @param string $name Listener
     * @return \Crud\Listener\BaseListener
     * @throws \Crud\Error\Exception\ListenerNotConfiguredException
     * @throws \Crud\Error\Exception\MissingListenerException
     */
    public function listener($name)
    {
        return $this->_loadListener($name);
    }

    /**
     * Add a new listener to Crud
     *
     * This will not load or initialize the listener, only lazy-load it.
     *
     * If `$name` is provided but no `$class` argument, the className will
     * be derived from the `$name`.
     *
     * CakePHP Plugin.ClassName format for `$name` and `$class` is supported.
     *
     * @param string $name Name
     * @param string $className Normal CakePHP plugin-dot annotation supported.
     * @param array $config Any default settings for a listener.
     * @return void
     */
    public function addListener($name, $className = null, $config = [])
    {
        if (strpos($name, '.') !== false) {
            list($plugin, $name) = pluginSplit($name);
            $className = $plugin . '.' . Inflector::camelize($name);
        }

        $name = Inflector::variable($name);
        $this->setConfig(sprintf('listeners.%s', $name), compact('className') + $config);
    }

    /**
     * Remove a listener from Crud.
     *
     * This will also detach it from the EventManager if it's attached.
     *
     * @param string $name Name
     * @return bool|null
     */
    public function removeListener($name)
    {
        $listeners = $this->getConfig('listeners');
        if (!array_key_exists($name, $listeners)) {
            return false;
        }

        if (isset($this->_listenerInstances[$name])) {
            $this->_eventManager->off($this->_listenerInstances[$name]);
            unset($this->_listenerInstances[$name]);
        }

        unset($listeners[$name]);
        $this->_config['listeners'] = $listeners;
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
     * @param string $eventName Event name
     * @param \Crud\Event\Subject|null $data Event data
     * @throws Exception if any event listener return a CakeResponse object.
     * @return \Cake\Event\Event
     */
    public function trigger($eventName, Subject $data = null)
    {
        $eventName = $this->_config['eventPrefix'] . '.' . $eventName;

        $Subject = $data ?: $this->getSubject();
        $Subject->addEvent($eventName);

        if (!empty($this->_config['eventLogging'])) {
            $this->logEvent($eventName, $data);
        }

        $Event = new Event($eventName, $Subject);
        $this->_eventManager->dispatch($Event);

        if ($Event->result instanceof Response) {
            $Exception = new Exception();
            $Exception->response = $Event->result;
            throw $Exception;
        }

        return $Event;
    }

    /**
     * Add a log entry for the event.
     *
     * @param string $eventName Event name
     * @param array $data Event data
     * @return void
     */
    public function logEvent($eventName, $data = [])
    {
        $this->_eventLog[] = [$eventName, $data];
    }

    /**
     * Set or get defaults for listeners and actions.
     *
     * @param string $type Can be anything, but 'listeners' or 'actions' is currently only used.
     * @param string|array $name The name of the $type - e.g. 'api', 'relatedModels'
     *  or an array ('api', 'relatedModels'). If $name is an array, the $config will be applied
     *  to each entry in the $name array.
     * @param mixed $config If NULL, the defaults is returned, else the defaults are changed.
     * @return mixed
     */
    public function defaults($type, $name, $config = null)
    {
        if ($config !== null) {
            if (!is_array($name)) {
                $name = [$name];
            }

            foreach ($name as $realName) {
                $this->setConfig(sprintf('%s.%s', $type, $realName), $config);
            }

            return null;
        }

        return $this->getConfig(sprintf('%s.%s', $type, $name));
    }

    /**
     * Returns an array of triggered events.
     *
     * @return array
     */
    public function eventLog()
    {
        return $this->_eventLog;
    }

    /**
     * Sets the model class to be used during the action execution.
     *
     * @param string $modelName The name of the model to load.
     * @return void
     */
    public function useModel($modelName)
    {
        $this->_controller->loadModel($modelName);
        list(, $this->_modelName) = pluginSplit($modelName);
    }

    /**
     * Returns controller's table instance.
     *
     * @return \Cake\ORM\Table
     */
    public function table()
    {
        return $this->_controller->{$this->_modelName};
    }

    /**
     * Returns new entity
     *
     * @param array $data Data
     * @return \Cake\Datasource\EntityInterface
     */
    public function entity(array $data = [])
    {
        return $this->table()->newEntity($data);
    }

    /**
     * Returns controller instance
     *
     * @return \Cake\Controller\Controller
     */
    public function controller()
    {
        return $this->_controller;
    }

    /**
     * Create a CakeEvent subject with the required properties.
     *
     * @param array $additional Additional properties for the subject.
     * @return \Crud\Event\Subject
     */
    public function getSubject($additional = [])
    {
        $subject = new Subject();
        $subject->set($additional);

        return $subject;
    }

    /**
     * Load all event classes attached to Crud.
     *
     * @return void
     * @throws \Crud\Error\Exception\ListenerNotConfiguredException
     * @throws \Crud\Error\Exception\MissingListenerException
     */
    protected function _loadListeners()
    {
        foreach (array_keys($this->getConfig('listeners')) as $name) {
            $this->_loadListener($name);
        }
    }

    /**
     * Load a single event class attached to Crud.
     *
     * @param string $name Name
     * @return \Crud\Listener\BaseListener
     * @throws \Crud\Error\Exception\ListenerNotConfiguredException
     * @throws \Crud\Error\Exception\MissingListenerException
     */
    protected function _loadListener($name)
    {
        if (!isset($this->_listenerInstances[$name])) {
            $config = $this->getConfig('listeners.' . $name);

            if (empty($config)) {
                throw new ListenerNotConfiguredException(sprintf('Listener "%s" is not configured', $name));
            }

            $className = App::className($config['className'], 'Listener', 'Listener');
            if (empty($className)) {
                throw new MissingListenerException('Could not find listener class: ' . $config['className']);
            }

            $this->_listenerInstances[$name] = new $className($this->_controller);
            unset($config['className']);
            $this->_listenerInstances[$name]->setConfig($config);

            $this->_eventManager->on($this->_listenerInstances[$name]);

            if (is_callable([$this->_listenerInstances[$name], 'setup'])) {
                $this->_listenerInstances[$name]->setup();
            }
        }

        return $this->_listenerInstances[$name];
    }

    /**
     * Load a CrudAction instance.
     *
     * @param string $name The controller action name.
     * @return \Crud\Action\BaseAction
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    protected function _loadAction($name)
    {
        if (!isset($this->_actionInstances[$name])) {
            $config = $this->getConfig('actions.' . $name);

            if (empty($config)) {
                throw new ActionNotConfiguredException(sprintf('Action "%s" has not been mapped', $name));
            }

            $className = App::className($config['className'], 'Action', 'Action');
            if (empty($className)) {
                throw new MissingActionException('Could not find action class: ' . $config['className']);
            }

            $this->_actionInstances[$name] = new $className($this->_controller);
            unset($config['className']);
            $this->_actionInstances[$name]->setConfig($config);
        }

        return $this->_actionInstances[$name];
    }
}
