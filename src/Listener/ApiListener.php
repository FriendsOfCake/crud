<?php
namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Error\ErrorHandler;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Crud\Error\ExceptionRenderer;
use Crud\Event\Subject;

/**
 * Enabled Crud to respond in a computer readable format like JSON or XML
 *
 * It tries to enforce some REST principles and keep some string conventions
 * in the output format
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiListener extends BaseListener
{

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'viewClasses' => [
            'json' => 'Json',
            'xml' => 'Xml'
        ],
        'detectors' => [
            'json' => ['ext' => 'json', 'accepts' => 'application/json'],
            'xml' => ['ext' => 'xml', 'accepts' => 'text/xml']
        ],
        'exception' => [
            'type' => 'default',
            'class' => BadRequestException::class,
            'message' => 'Unknown error',
            'code' => 0
        ],
        'exceptionRenderer' => ExceptionRenderer::class,
        'setFlash' => false
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * We attach at priority 10 so normal bound events can run before us
     *
     * @return array
     */
    public function implementedEvents()
    {
        $this->setupDetectors();

        if (!$this->_checkRequestType('api')) {
            return [];
        }

        return [
            'Crud.beforeHandle' => ['callable' => [$this, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$this, 'setFlash'], 'priority' => 5],

            'Crud.beforeRender' => ['callable' => [$this, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$this, 'respond'], 'priority' => 100]
        ];
    }

    /**
     * setup
     *
     * Called when the listener is created
     *
     * @return void
     */
    public function setup()
    {
        if (!$this->_checkRequestType('api')) {
            return;
        }

        $appClass = Configure::read('App.namespace') . '\Application';

        // If `App\Application` class exists it means Cake 3.3's PSR7 middleware
        // implementation is used and it's too late to register new error handler.
        if (!class_exists($appClass, false)) {
            $this->registerExceptionHandler();
        }
    }

    /**
     * beforeHandle
     *
     * Called before the crud action is executed
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandle(Event $event)
    {
        $this->_checkRequestMethods();
    }

    /**
     * Handle response
     *
     * @param \Cake\Event\Event $event Event
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function respond(Event $event)
    {
        $key = $event->getSubject()->success ? 'success' : 'error';
        $apiConfig = $this->_action()->getConfig('api.' . $key);

        if (isset($apiConfig['exception'])) {
            $this->_exceptionResponse($event, $apiConfig['exception']);

            return null;
        }

        $response = $this->render($event->getSubject());

        if (empty($apiConfig['code'])) {
            return $response;
        }

        return $response->withStatus($apiConfig['code']);
    }

    /**
     * Check for allowed HTTP request types
     *
     * @throws \Cake\Http\Exception\MethodNotAllowedException
     * @return bool
     */
    protected function _checkRequestMethods()
    {
        $action = $this->_action();
        $apiConfig = $action->getConfig('api');

        if (!isset($apiConfig['methods'])) {
            return false;
        }

        $request = $this->_request();
        foreach ($apiConfig['methods'] as $method) {
            if ($request->is($method)) {
                return true;
            }
        }

        throw new MethodNotAllowedException();
    }

    /**
     * Register the Crud exception handler
     *
     * @return void
     */
    public function registerExceptionHandler()
    {
        $exceptionRenderer = $this->getConfig('exceptionRenderer');
        (new ErrorHandler(compact('exceptionRenderer') + (array)Configure::read('Error')))->register();
    }

    /**
     * Throw an exception based on API configuration
     *
     * @param \Cake\Event\Event $Event Event
     * @param array $exceptionConfig Exception config
     * @return void
     * @throws \Exception
     */
    protected function _exceptionResponse(Event $Event, $exceptionConfig)
    {
        $exceptionConfig = array_merge($this->getConfig('exception'), $exceptionConfig);

        $class = $exceptionConfig['class'];

        if ($exceptionConfig['type'] === 'validate') {
            $exception = new $class($Event->getSubject()->entity);
            throw $exception;
        }

        $exception = new $class($exceptionConfig['message'], $exceptionConfig['code']);
        throw $exception;
    }

    /**
     * Selects an specific Crud view class to render the output
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\Http\Response
     */
    public function render(Subject $subject)
    {
        $this->injectViewClasses();
        $this->_ensureSuccess($subject);
        $this->_ensureData($subject);
        $this->_ensureSerialize();

        return $this->_controller()->render();
    }

    /**
     * Ensure _serialize is set in the view
     *
     * @return void
     */
    protected function _ensureSerialize()
    {
        $controller = $this->_controller();

        if (isset($controller->viewVars['_serialize'])) {
            return;
        }

        $serialize = [];
        $serialize[] = 'success';

        $action = $this->_action();
        if (method_exists($action, 'viewVar')) {
            $serialize['data'] = $action->viewVar();
        } else {
            $serialize[] = 'data';
        }

        $serialize = array_merge($serialize, (array)$action->getConfig('serialize'));
        $controller->set('_serialize', $serialize);
    }

    /**
     * Ensure success key is present in Controller::$viewVars
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return void
     */
    protected function _ensureSuccess(Subject $subject)
    {
        $controller = $this->_controller();

        if (isset($controller->viewVars['success'])) {
            return;
        }

        $controller->set('success', $subject->success);
    }

    /**
     * Ensure data key is present in Controller:$viewVars
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return void
     */
    protected function _ensureData(Subject $subject)
    {
        $controller = $this->_controller();
        $action = $this->_action();

        if (method_exists($action, 'viewVar')) {
            $viewVar = $action->viewVar();
        } else {
            $viewVar = 'data';
        }

        if (isset($controller->viewVars[$viewVar])) {
            return;
        }

        $key = $subject->success ? 'success' : 'error';

        $config = $action->getConfig('api.' . $key);

        $data = [];

        if (isset($config['data']['subject'])) {
            $config['data']['subject'] = Hash::normalize((array)$config['data']['subject']);

            $subjectArray = (array)$subject;
            foreach ($config['data']['subject'] as $keyPath => $valuePath) {
                if ($valuePath === null) {
                    $valuePath = $keyPath;
                }

                $keyPath = $this->_expandPath($subject, $keyPath);
                $valuePath = $this->_expandPath($subject, $valuePath);

                $data = Hash::insert($data, $keyPath, Hash::get($subjectArray, $valuePath));
            }
        }

        if (isset($config['data']['entity'])) {
            $config['data']['entity'] = Hash::normalize((array)$config['data']['entity']);

            foreach ($config['data']['entity'] as $keyPath => $valuePath) {
                if ($valuePath === null) {
                    $valuePath = $keyPath;
                }

                if (method_exists($subject->entity, $valuePath)) {
                    $data = Hash::insert($data, $keyPath, call_user_func([$subject->entity, $valuePath]));
                } elseif (isset($subject->entity->{$valuePath})) {
                    $data = Hash::insert($data, $keyPath, $subject->entity->{$valuePath});
                }
            }
        }

        if (isset($config['data']['raw'])) {
            foreach ($config['data']['raw'] as $path => $value) {
                $path = $this->_expandPath($subject, $path);
                $data = Hash::insert($data, $path, $value);
            }
        }

        if (method_exists($action, 'viewVar')) {
            $viewVar = $action->viewVar();
        } else {
            $viewVar = 'data';
        }

        $controller->set($viewVar, $data);
    }

    /**
     * Expand all scalar values from a CrudSubject
     * and use them for a Text::insert() interpolation
     * of a path
     *
     * @param \Crud\Event\Subject $subject Subject
     * @param string $path Path
     * @return string
     */
    protected function _expandPath(Subject $subject, $path)
    {
        $keys = [];
        $subjectArray = (array)$subject;

        foreach (array_keys($subjectArray) as $key) {
            if (!is_scalar($subjectArray[$key])) {
                continue;
            }

            $keys[$key] = $subjectArray[$key];
        }

        return Text::insert($path, $keys, ['before' => '{', 'after' => '}']);
    }

    /**
     * Inject view classes into RequestHandler
     *
     * @return void
     */
    public function injectViewClasses()
    {
        $controller = $this->_controller();
        foreach ($this->getConfig('viewClasses') as $type => $class) {
            $controller->RequestHandler->setConfig('viewClassMap', [$type => $class]);
        }
    }

    /**
     * Get or set a viewClass
     *
     * `$type` could be `json`, `xml` or any other valid type
     *      defined by the `RequestHandler`
     *
     * `$class` could be any View class capable of handling
     *      the response format for the `$type`. Normal
     *      CakePHP plugin "dot" notation is supported
     *
     * @param string $type Type
     * @param string|null $class Class name
     * @return mixed
     */
    public function viewClass($type, $class = null)
    {
        if ($class === null) {
            return $this->getConfig('viewClasses.' . $type);
        }

        return $this->setConfig('viewClasses.' . $type, $class);
    }

    /**
     * setFlash
     *
     * An API request doesn't need flash messages - so stop them being processed
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function setFlash(Event $event)
    {
        if (!$this->getConfig('setFlash')) {
            $event->stopPropagation();
        }
    }

    /**
     * Setup detectors
     *
     * Both detects on two signals:
     *  1) The extension in the request (e.g. /users/index.$ext)
     *  2) The accepts header from the client
     *
     * There is a combined request detector for all detectors called 'api'
     *
     * @return void
     */
    public function setupDetectors()
    {
        $request = $this->_request();
        $detectors = $this->getConfig('detectors');

        foreach ($detectors as $name => $config) {
            $request->addDetector($name, function (ServerRequest $request) use ($config) {
                if ($config['ext'] !== false && $request->getParam('_ext') === $config['ext']) {
                    return true;
                }

                return $request->accepts($config['accepts']);
            });
        }

        $request->addDetector('api', function (ServerRequest $request) use ($detectors) {
            foreach ($detectors as $name => $config) {
                if ($request->is($name)) {
                    return true;
                }
            }

            return false;
        });
    }
}
