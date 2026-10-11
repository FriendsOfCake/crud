<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\View\JsonView;
use Cake\View\XmlView;
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
    protected array $defaultConfig = [
        'viewClasses' => [
            'json' => JsonView::class,
            'xml' => XmlView::class,
        ],
        'detectors' => [
            'json' => ['accept' => ['application/json'], 'param' => '_ext', 'value' => 'json'],
            'xml' => [
                'accept' => ['application/xml', 'text/xml'],
                'exclude' => ['text/html'],
                'param' => '_ext',
                'value' => 'xml',
            ],
        ],
        'exception' => [
            'type' => 'default',
            'class' => BadRequestException::class,
            'message' => 'Unknown error',
            'code' => 0,
        ],
        'setFlash' => false,
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * We attach at priority 10 so normal bound events can run before us
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        $this->setupDetectors();

        if (!$this->checkRequestType('api')) {
            return [];
        }

        return [
            'Crud.beforeHandle' => ['callable' => [$this, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$this, 'setFlash'], 'priority' => 5],

            'Crud.beforeRender' => ['callable' => [$this, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$this, 'respond'], 'priority' => 100],
        ];
    }

    /**
     * beforeHandle
     *
     * Called before the crud action is executed
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return void
     */
    public function beforeHandle(EventInterface $event): void
    {
        $this->checkRequestMethods();
    }

    /**
     * Handle response
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function respond(EventInterface $event): ?Response
    {
        $subject = $event->getSubject();
        assert($subject instanceof Subject);

        $key = $subject->success ? 'success' : 'error';
        $apiConfig = $this->action()->getConfig('api.' . $key);

        if (isset($apiConfig['exception'])) {
            $this->exceptionResponse($event, $apiConfig['exception']);

            return null;
        }

        $response = $this->render($subject);

        if (!empty($apiConfig['code'])) {
            $response = $response->withStatus($apiConfig['code']);
        }

        $event->setResult($response);

        return null;
    }

    /**
     * Check for allowed HTTP request types
     *
     * @return void
     * @throws \Cake\Http\Exception\MethodNotAllowedException
     */
    protected function checkRequestMethods(): void
    {
        $action = $this->action();
        $apiConfig = $action->getConfig('api');

        if (!isset($apiConfig['methods'])) {
            return;
        }

        $request = $this->request();
        foreach ($apiConfig['methods'] as $method) {
            if ($request->is($method)) {
                return;
            }
        }

        throw new MethodNotAllowedException();
    }

    /**
     * Throw an exception based on API configuration
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $Event Event
     * @param array $exceptionConfig Exception config
     * @return void
     * @throws \Exception
     */
    protected function exceptionResponse(EventInterface $Event, array $exceptionConfig): void
    {
        $exceptionConfig = array_merge($this->getConfig('exception'), $exceptionConfig);

        $class = $exceptionConfig['class'];

        if ($exceptionConfig['type'] === 'validate') {
            $subject = $Event->getSubject();
            assert($subject instanceof Subject);

            /** @var \Exception $exception */
            $exception = new $class($subject->entity);
            throw $exception;
        }

        /** @var \Exception $exception */
        $exception = new $class($exceptionConfig['message'], $exceptionConfig['code']);
        throw $exception;
    }

    /**
     * Selects an specific Crud view class to render the output
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\Http\Response
     */
    public function render(Subject $subject): Response
    {
        $this->injectViewClasses();
        $this->ensureSuccess($subject);
        $this->ensureData($subject);
        $this->ensureSerialize();

        return $this->controller()->render();
    }

    /**
     * Ensure _serialize is set in the view
     *
     * @return void
     */
    protected function ensureSerialize(): void
    {
        $controller = $this->controller();

        if ($controller->viewBuilder()->getOption('serialize') !== null) {
            return;
        }

        $serialize = [];
        $serialize[] = 'success';

        $action = $this->action();
        if (method_exists($action, 'viewVar')) {
            $serialize['data'] = $action->viewVar();
        } else {
            $serialize[] = 'data';
        }

        $serialize = array_merge($serialize, (array)$action->getConfig('serialize'));
        $controller->viewBuilder()->setOption('serialize', $serialize);
    }

    /**
     * Ensure success key is present in Controller::$viewVars
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return void
     */
    protected function ensureSuccess(Subject $subject): void
    {
        $controller = $this->controller();

        if ($controller->viewBuilder()->getVar('success') !== null) {
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
    protected function ensureData(Subject $subject): void
    {
        $controller = $this->controller();
        $action = $this->action();

        if (method_exists($action, 'viewVar')) {
            $viewVar = $action->viewVar();
        } else {
            $viewVar = 'data';
        }

        if ($controller->viewBuilder()->getVar($viewVar) !== null) {
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

                $keyPath = $this->expandPath($subject, $keyPath);
                $valuePath = $this->expandPath($subject, $valuePath);

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
                    /** @phpstan-ignore argument.type */
                    $data = Hash::insert($data, $keyPath, call_user_func([$subject->entity, $valuePath]));
                } elseif (isset($subject->entity->{$valuePath})) {
                    $data = Hash::insert($data, $keyPath, $subject->entity->{$valuePath});
                }
            }
        }

        if (isset($config['data']['raw'])) {
            foreach ($config['data']['raw'] as $path => $value) {
                $path = $this->expandPath($subject, $path);
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
    protected function expandPath(Subject $subject, string $path): string
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
     * Add view classes to the controller
     *
     * @return void
     */
    public function injectViewClasses(): void
    {
        $this->controller()->addViewClasses($this->getConfig('viewClasses'));
    }

    /**
     * Get or set a viewClass
     *
     * `$type` could be `json`, `xml` or any other valid configured type
     *
     * `$class` could be any View class capable of handling
     *      the response format for the `$type`. Normal
     *      CakePHP plugin "dot" notation is supported
     *
     * @param string $type Type
     * @param string|null $class Class name
     * @return mixed
     */
    public function viewClass(string $type, ?string $class = null): mixed
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
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return void
     */
    public function setFlash(EventInterface $event): void
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
    public function setupDetectors(): void
    {
        $request = $this->request();
        $detectors = $this->getConfig('detectors');

        foreach ($detectors as $name => $config) {
            $request->addDetector($name, $config);
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
