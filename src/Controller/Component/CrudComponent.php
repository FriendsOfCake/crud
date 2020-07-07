<?php
declare(strict_types=1);

namespace Crud\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Crud\Action\BaseAction;
use Crud\Error\Exception\ActionNotConfiguredException;
use Crud\Error\Exception\CrudException;
use Crud\Error\Exception\ListenerNotConfiguredException;
use Crud\Error\Exception\MissingActionException;
use Crud\Error\Exception\MissingListenerException;
use Crud\Event\Subject;
use Crud\Listener\BaseListener;
use Psr\Http\Message\ResponseInterface;

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
     * @var \Cake\Event\EventManagerInterface
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
     * @var \Crud\Listener\BaseListener[]
     */
    protected $_listenerInstances = [];

    /**
     * List of crud actions.
     *
     * @var \Crud\Action\BaseAction[]
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
                'text' => 'Invalid id',
            ],
            'recordNotFound' => [
                'code' => 404,
                'class' => NotFoundException::class,
                'text' => 'Not found',
            ],
            'badRequestMethod' => [
                'code' => 405,
                'class' => MethodNotAllowedException::class,
                'text' => 'Method not allowed. This action permits only {methods}',
            ],
        ],
        'eventLogging' => false,
    ];

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $collection A ComponentCollection this component
     *   can use to lazy load its components.
     * @param array $config Array of configuration settings.
     */
    public function __construct(ComponentRegistry $collection, array $config = [])
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
    public function normalizeArray(array $array): array
    {
        $normal = [];

        foreach ($array as $action => $config) {
            if (is_string($config)) {
                $config = ['className' => $config];
            }

            if (is_int($action)) {
                [, $action] = pluginSplit($config['className']);
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
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_action = $this->getController()->getRequest()->getParam('action');
    }

    /**
     * Loads listeners
     *
     * @param \Cake\Event\EventInterface $event Event instance
     * @return void
     * @throws \Exception
     */
    public function beforeFilter(EventInterface $event): void
    {
        $this->_loadListeners();
        $this->trigger('beforeFilter');
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action.
     *
     * @param \Cake\Event\EventInterface $event Event instance
     * @return void
     * @throws \Exception
     */
    public function startup(EventInterface $event): void
    {
        $this->_loadListeners();
        $this->trigger('startup');
    }

    /**
     * Execute a Crud action
     *
     * @param string|null $controllerAction Override the controller action to execute as.
     * @param array $args List of arguments to pass to the CRUD action (Usually an ID to edit / delete).
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception If an action is not mapped.
     */
    public function execute(?string $controllerAction = null, array $args = []): ResponseInterface
    {
        $this->_loadListeners();

        $this->_action = $controllerAction ?: $this->_action;

        $action = $this->_action;
        if (empty($args)) {
            $args = $this->getController()->getRequest()->getParam('pass');
        }

        try {
            $event = $this->trigger('beforeHandle', $this->getSubject(compact('args', 'action')));

            $response = $this->action($event->getSubject()->action)->handle($event->getSubject()->args);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        } catch (CrudException $e) {
            $response = $e->getResponse();
            if ($response !== null) {
                return $response;
            }

            throw $e;
        }

        $view = null;
        $crudAction = $this->action($action);
        if (method_exists($crudAction, 'view')) {
            $view = $crudAction->view();
        }

        return $this->_controller->render($view);
    }

    /**
     * Get a CrudAction object by action name.
     *
     * @param string|null $name The controller action name.
     * @return \Crud\Action\BaseAction
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     * @throws \Crud\Error\Exception\MissingActionException
     */
    public function action(?string $name = null): BaseAction
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
    public function enable($actions): void
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
    public function disable($actions): void
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
    public function view($action, ?string $view = null): void
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realView) {
                /** @psalm-suppress UndefinedMethod */
                $this->action($realAction)->view($realView);
            }

            return;
        }

        /** @psalm-suppress UndefinedMethod */
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
    public function viewVar($action, ?string $viewVar = null): void
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realViewVar) {
                /** @psalm-suppress UndefinedMethod */
                $this->action($realAction)->viewVar($realViewVar);
            }

            return;
        }

        /** @psalm-suppress UndefinedMethod */
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
    public function findMethod($action, ?string $method = null): void
    {
        if (is_array($action)) {
            foreach ($action as $realAction => $realMethod) {
                /** @psalm-suppress UndefinedMethod */
                $this->action($realAction)->findMethod($realMethod);
            }

            return;
        }

        /** @psalm-suppress UndefinedMethod */
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
    public function mapAction(string $action, $config = [], bool $enable = true): void
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
    public function isActionMapped(?string $action = null): bool
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
    public function on($events, callable $callback, array $options = []): void
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
    public function listener(string $name): BaseListener
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
     * @param string|null $className Normal CakePHP plugin-dot annotation supported.
     * @param array $config Any default settings for a listener.
     * @return void
     */
    public function addListener(string $name, ?string $className = null, array $config = []): void
    {
        if (strpos($name, '.') !== false) {
            [$plugin, $name] = pluginSplit($name);
            /** @psalm-suppress PossiblyNullOperand */
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
     * @return bool
     */
    public function removeListener(string $name): bool
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

        return true;
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
     * @param \Crud\Event\Subject|array|null $data Event subject / data
     * @throws \Exception if any event listener return a CakeResponse object.
     * @return \Cake\Event\EventInterface
     */
    public function trigger(string $eventName, $data = null): EventInterface
    {
        $eventName = $this->_config['eventPrefix'] . '.' . $eventName;

        $Subject = $data instanceof Subject ? $data : $this->getSubject($data ?? []);
        $Subject->addEvent($eventName);

        if (!empty($this->_config['eventLogging'])) {
            $this->logEvent($eventName, $Subject);
        }

        $Event = new Event($eventName, $Subject);
        $Event = $this->_eventManager->dispatch($Event);

        if ($Event->getResult() instanceof ResponseInterface) {
            $Exception = new CrudException();
            $Exception->setResponse($Event->getResult());
            throw $Exception;
        }

        return $Event;
    }

    /**
     * Add a log entry for the event.
     *
     * @param string $eventName Event name
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    public function logEvent(string $eventName, Subject $subject): void
    {
        $this->_eventLog[] = [$eventName, $subject];
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
    public function defaults(string $type, $name, $config = null)
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

        /** @psalm-suppress PossiblyInvalidArgument */
        return $this->getConfig(sprintf('%s.%s', $type, $name));
    }

    /**
     * Returns an array of triggered events.
     *
     * @return array
     */
    public function eventLog(): array
    {
        return $this->_eventLog;
    }

    /**
     * Sets the model class to be used during the action execution.
     *
     * @param string $modelName The name of the model to load.
     * @return void
     */
    public function useModel(string $modelName): void
    {
        $this->_controller->loadModel($modelName);
        [, $this->_modelName] = pluginSplit($modelName);
    }

    /**
     * Returns controller's table instance.
     *
     * @return \Cake\ORM\Table
     */
    public function table(): Table
    {
        return $this->_controller->{$this->_modelName};
    }

    /**
     * Returns new entity
     *
     * @param array $data Data
     * @return \Cake\Datasource\EntityInterface
     */
    public function entity(array $data = []): EntityInterface
    {
        return $this->table()->newEntity($data);
    }

    /**
     * Returns controller instance
     *
     * @return \Cake\Controller\Controller
     */
    public function controller(): Controller
    {
        return $this->_controller;
    }

    /**
     * Create a CakeEvent subject with the required properties.
     *
     * @param array $additional Additional properties for the subject.
     * @return \Crud\Event\Subject
     */
    public function getSubject(array $additional = []): Subject
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
    protected function _loadListeners(): void
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
    protected function _loadListener(string $name): BaseListener
    {
        if (!isset($this->_listenerInstances[$name])) {
            $config = $this->getConfig('listeners.' . $name);

            if (empty($config)) {
                throw new ListenerNotConfiguredException(sprintf('Listener "%s" is not configured', $name));
            }

            /** @psalm-var class-string<\Crud\Listener\BaseListener>|null $className */
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
    protected function _loadAction(string $name): BaseAction
    {
        if (!isset($this->_actionInstances[$name])) {
            $config = $this->getConfig('actions.' . $name);

            if (empty($config)) {
                throw new ActionNotConfiguredException(sprintf('Action "%s" has not been mapped', $name));
            }

            /** @psalm-var class-string<\Crud\Action\BaseAction>|null */
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
