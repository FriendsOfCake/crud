<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Component\RequestHandlerComponent;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Crud\Action\AddAction;
use Crud\Action\BaseAction;
use Crud\Action\DeleteAction;
use Crud\Action\IndexAction;
use Crud\Action\ViewAction;
use Crud\Event\Subject;
use Crud\Listener\ApiListener;
use Crud\Test\App\Controller\BlogsController;
use Crud\TestSuite\TestCase;
use StdClass;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiListenerTest extends TestCase
{
    /**
     * Test implementedEvents with API request
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['setupDetectors', '_checkRequestType'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('setupDetectors');
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(true));

        $expected = [
            'Crud.beforeHandle' => ['callable' => [$listener, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$listener, 'setFlash'], 'priority' => 5],

            'Crud.beforeRender' => ['callable' => [$listener, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$listener, 'respond'], 'priority' => 100],
        ];
        $result = $listener->implementedEvents();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test implementedEvents without API request
     *
     * @return void
     */
    public function testImplementedEventsWithoutApi()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['setupDetectors', '_checkRequestType'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('setupDetectors');
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(false));

        $expected = [];
        $result = $listener->implementedEvents();
        $this->assertEquals($expected, $result);
    }

    /**
     * testBeforeHandle
     *
     * @return void
     */
    public function testBeforeHandle()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_checkRequestMethods'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_checkRequestMethods');

        $listener->beforeHandle(new Event('Crud.beforeHandle'));
    }

    /**
     * Test response method
     *
     * @return void
     */
    public function testResponse()
    {
        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();

        $response = $this
            ->getMockBuilder(Response::class)
            ->onlyMethods(['withStatus'])
            ->getMock();

        $subject = $this
            ->getMockBuilder(Subject::class)
            ->getMock();
        $subject->success = true;

        $event = new Event('Crud.afterSave', $subject);

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_action', 'render'])
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $action
            ->expects($this->nextCounter($action))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue(['code' => 200]));
        $listener
            ->expects($this->nextCounter($listener))
            ->method('render')
            ->with($subject)
            ->will($this->returnValue($response));
        $response
            ->expects($this->nextCounter($response))
            ->method('withStatus')
            ->with(200);

        $listener->respond($event);
    }

    /**
     * @see https://github.com/FriendsOfCake/crud/issues/642
     * @return void
     */
    public function testResponseDeleteError()
    {
        $action = $this
            ->getMockBuilder(DeleteAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['handle'])
            ->getMock();

        $response = $this
            ->getMockBuilder(Response::class)
            ->onlyMethods(['withStatus'])
            ->getMock();

        $subject = $this
            ->getMockBuilder(Subject::class)
            ->getMock();
        $subject->success = false;

        $event = new Event('Crud.beforeRedirect', $subject);

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_action', 'render'])
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $listener
            ->expects($this->nextCounter($listener))
            ->method('render')
            ->with($subject)
            ->will($this->returnValue($response));
        $response
            ->expects($this->nextCounter($response))
            ->method('withStatus')
            ->with(400);

        $listener->respond($event);
    }

    /**
     * testResponseWithStatusCodeNotSpecified
     *
     * @return void
     * @see https://github.com/FriendsOfCake/crud/issues/572
     */
    public function testResponseWithStatusCodeNotSpecified()
    {
        $action = $this
            ->getMockBuilder(ViewAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();

        $response = $this
            ->getMockBuilder(Response::class)
            ->onlyMethods(['withStatus'])
            ->getMock();

        $subject = $this
            ->getMockBuilder(Subject::class)
            ->getMock();
        $subject->success = true;

        $event = new Event('Crud.afterSave', $subject);

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_action', 'render'])
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $action
            ->expects($this->nextCounter($action))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue(null));
        $listener
            ->expects($this->nextCounter($listener))
            ->method('render')
            ->with($subject)
            ->will($this->returnValue($response));
        $response
            ->expects($this->never())
            ->method('withStatus');

        $response = $listener->respond($event);
    }

    /**
     * Test response method with exception config
     *
     * @return void
     */
    public function testResponseWithExceptionConfig()
    {
        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();

        $subject = $this->getMockBuilder(Subject::class)
            ->getMock();
        $subject->success = true;

        $event = new Event('Crud.afterSave', $subject);

        $i = 0;

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_action', 'render', '_exceptionResponse'])
            ->getMock();
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $action
            ->expects($this->nextCounter($action))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue(['exception' => ['SomethingExceptional']]));
        $listener
            ->expects($this->nextCounter($listener))
            ->method('_exceptionResponse')
            ->with($event, ['SomethingExceptional']);
        $listener
            ->expects($this->never())
            ->method('render');

        $listener->respond($event);
    }

    /**
     * Test default configuration
     *
     * @return void
     */
    public function testDefaultConfiguration()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $expected = [
            'viewClasses' => [
                'json' => 'Json',
                'xml' => 'Xml',
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
                'class' => 'Cake\Http\Exception\BadRequestException',
                'message' => 'Unknown error',
                'code' => 0,
            ],
            'setFlash' => false,
        ];
        $result = $listener->getConfig();
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testExceptionResponse
     *
     * @return array
     */
    public function dataExceptionResponse()
    {
        return [
            'default configuration' => [
                [],
                '\Cake\Http\Exception\BadRequestException',
                'Unknown error',
                0,
            ],

            'change exception class' => [
                ['class' => '\Cake\Core\Exception\Exception'],
                '\Cake\Core\Exception\Exception',
                'Unknown error',
                0,
            ],

            'change exception code' => [
                ['code' => 10],
                '\Cake\Http\Exception\BadRequestException',
                'Unknown error',
                10,
            ],

            'change exception message' => [
                ['message' => 'epic message'],
                '\Cake\Http\Exception\BadRequestException',
                'epic message',
                0,
            ],

            'Validate case #1 - no validation errors' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                '0 validation errors occurred',
                422,
            ],

            'Validate case #2 - one validation error' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                'A validation error occurred',
                422,
                [['id' => 'hello world']],
            ],

            'Validate case #3 - two validation errors' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                '2 validation errors occurred',
                422,
                [['id' => 'hello world', 'name' => 'fail me']],
            ],
        ];
    }

    /**
     * Test _exceptionResponse
     *
     * @dataProvider dataExceptionResponse
     * @param array $apiConfig
     * @param string $exceptionClass
     * @param string $exceptionMessage
     * @param int $exceptionCode
     * @param array $validationErrors
     * @return void
     */
    public function testExceptionResponse(
        $apiConfig,
        $exceptionClass,
        $exceptionMessage,
        $exceptionCode,
        $validationErrors = []
    ) {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $event = new Event('Crud.Exception', new Subject());

        if (isset($apiConfig['type']) && $apiConfig['type'] === 'validate') {
            $event->getSubject()->set([
                'entity' => $this->getMockBuilder(Entity::class)
                    ->onlyMethods(['getErrors'])
                    ->getMock(),
            ]);

            $event->getSubject()->entity
                ->expects($this->any())
                ->method('getErrors')
                ->will($this->returnValue($validationErrors));
        }

        $this->expectException($exceptionClass, $exceptionMessage, $exceptionCode);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_exceptionResponse', [$event, $apiConfig], $listener);
    }

    /**
     * testEnsureSerializeWithViewVar
     *
     * @return void
     */
    public function testEnsureSerializeWithViewVar()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->onlyMethods(['viewVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('viewVar')
            ->will($this->returnValue('items'));

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);

        $this->assertEquals(
            ['success', 'data' => 'items'],
            $controller->viewBuilder()->getOption('serialize')
        );
    }

    /**
     * Data provider for testExpandPath
     *
     * @return array
     */
    public function dataSerializeTraitActions()
    {
        return [
            'View Action' => ['\Crud\Action\ViewAction'],
            'Index Action' => ['\Crud\Action\IndexAction'],
            'Edit Action' => ['\Crud\Action\EditAction'],
            'Add Action' => ['\Crud\Action\AddAction'],
        ];
    }

    /**
     * Test SerializeTrait
     *
     * @dataProvider dataSerializeTraitActions
     * @return void
     */
    public function testEnsureSerializeWithSerializeTrait($action)
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder($action)
            ->onlyMethods(['setConfig', 'getConfig', 'viewVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $i = 0;
        $action
            ->expects($this->at($i++))
            ->method('setConfig')
            ->with('serialize', ['something']);
        $action
            ->expects($this->at($i++))
            ->method('viewVar')
            ->will($this->returnValue(null));
        $action
            ->expects($this->once())
            ->method('getConfig')
            ->with('serialize')
            ->will($this->returnValue(['something']));

        $this->setReflectionClassInstance($action);
        $this->callProtectedMethod('serialize', [['something']], $action);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);

        $this->assertEquals(
            ['success', 'something', 'data' => null],
            $controller->viewBuilder()->getOption('serialize')
        );
    }

    /**
     * testEnsureSerializeAlreadySet
     *
     * @return void
     */
    public function testEnsureSerializeAlreadySet()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->viewBuilder()->setOption('serialize', 'hello world');

        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->onlyMethods(['viewVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->never())
            ->method('_action');
        $action
            ->expects($this->never())
            ->method('viewVar');
        $controller
            ->expects($this->never())
            ->method('set');

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);

        $this->assertEquals(
            'hello world',
            $controller->viewBuilder()->getOption('serialize')
        );
    }

    /**
     * testEnsureSerializeWithViewVarChanged
     *
     * @return void
     */
    public function testEnsureSerializeWithViewVarChanged()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->onlyMethods(['viewVar'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('viewVar')
            ->will($this->returnValue('helloWorld'));

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);

        $this->assertEquals(
            ['success', 'data' => 'helloWorld'],
            $controller->viewBuilder()->getOption('serialize')
        );
    }

    /**
     * testEnsureSerializeWithoutViewVar
     *
     * @return void
     */
    public function testEnsureSerializeWithoutViewVar()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(BlogsController::class)
            ->onlyMethods(['set', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(AddAction::class)
            ->onlyMethods(['scope', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $controller
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(''));
        $action->expects($this->any())
            ->method('scope')
            ->will($this->returnValue('table'));
        $action->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);

        $this->assertEquals(
            ['success', 'data' => ''],
            $controller->viewBuilder()->getOption('serialize')
        );
    }

    /**
     * testEnsureSuccess
     *
     * @return void
     */
    public function testEnsureSuccess()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => true]);

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('success', true);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSuccess', [$subject], $listener);
    }

    /**
     * testEnsureData
     *
     * @return void
     */
    public function testEnsureData()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => true]);

        $config = [];

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue($config));
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('data', []);

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_ensureData', [$subject], $listener);
    }

    /**
     * testEnsureDataSubject
     *
     * @return void
     */
    public function testEnsureDataSubject()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => true, 'id' => 1, 'modelClass' => 'MyModel']);

        $config = ['data' => [
            'subject' => [
                '{modelClass}.id' => 'id',
                'modelClass',
            ],
        ]];

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue($config));
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('data', ['modelClass' => 'MyModel', 'MyModel' => ['id' => 1]]);

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_ensureData', [$subject], $listener);
    }

    /**
     * testEnsureDataRaw
     *
     * @return void
     */
    public function testEnsureDataRaw()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => true, 'id' => 1, 'modelClass' => 'MyModel']);

        $config = ['data' => ['raw' => ['{modelClass}.id' => 1]]];

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('getConfig')
            ->with('api.success')
            ->will($this->returnValue($config));
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('data', ['MyModel' => ['id' => 1]]);

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_ensureData', [$subject], $listener);
    }

    /**
     * testEnsureDataError
     *
     * @return void
     */
    public function testEnsureDataError()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => false]);

        $config = [];

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->at($i++))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('getConfig')
            ->with('api.error')
            ->will($this->returnValue($config));
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('data', []);

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_ensureData', [$subject], $listener);
    }

    /**
     * testEnsureSuccessAlreadySet
     *
     * @return void
     */
    public function testEnsureSuccessAlreadySet()
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject(['success' => true]);

        $controller = $this
            ->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->viewBuilder()->setVar('success', true);

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_controller')
            ->will($this->returnValue($controller));
        $controller
            ->expects($this->never())
            ->method('set');

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSuccess', [$subject], $listener);
    }

    /**
     * testFlashMessageSupressed
     *
     * The API listener should suppress flash messages
     * if the request is "API"
     *
     * @return void
     */
    public function testFlashMessageSupressed()
    {
        $Request = new ServerRequest();
        $Request->addDetector('api', ['callback' => function () {
            return true;
        }]);

        $subject = new Subject(['request' => $Request]);

        $apiListener = $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $event = new Event('Crud.setFlash', $subject);
        $apiListener->setFlash($event);

        $stopped = $event->isStopped();
        $this->assertTrue($stopped, 'Set flash event is expected to be stopped');
    }

    /**
     * testFlashMessageEnabled
     *
     * There are use cases where you want to
     * enable flash messages.
     *
     * @return void
     */
    public function testFlashMessageEnabled()
    {
        $Request = new ServerRequest();
        $Request->addDetector('api', ['callback' => function () {
            return true;
        }]);

        $subject = new Subject(['request' => $Request]);

        $apiListener = $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $event = new Event('Crud.setFlash', $subject);
        $apiListener->setConfig(['setFlash' => true]);
        $apiListener->setFlash($event);

        $stopped = $event->isStopped();
        $this->assertFalse($stopped);
    }

    /**
     * Data provider for testExpandPath
     *
     * @return array
     */
    public function dataExpandPath()
    {
        return [
            'simple string' => [
                new Subject(['modelClass' => 'MyModel']),
                '{modelClass}.id',
                'MyModel.id',
            ],

            'string and integer' => [
                new Subject(['modelClass' => 'MyModel', 'id' => 1]),
                '{modelClass}.{id}',
                'MyModel.1',
            ],

            'ignore non scalar' => [
                new Subject(['modelClass' => 'MyModel', 'complex' => new StdClass()]),
                '{modelClass}.{id}',
                'MyModel.{id}',
            ],
        ];
    }

    /**
     * testExpandPath
     *
     * @dataProvider dataExpandPath
     * @return void
     */
    public function testExpandPath($subject, $path, $expected)
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_expandPath', [$subject, $path], $listener);
        $this->assertSame($expected, $result);
    }

    /**
     * testSetupDetectors
     *
     * @return void
     */
    public function testSetupDetectors()
    {
        $this->skipIf(true);

        $detectors = ['xml' => [], 'json' => []];

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_request', 'config'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['addDetector'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_request')
            ->will($this->returnValue($request));
        $listener
            ->expects($this->at($i++))
            ->method('config')
            ->with('detectors')
            ->will($this->returnValue($detectors));

        $r = 0;
        foreach ($detectors as $name => $config) {
            $request
                ->expects($this->at($r++))
                ->method('addDetector')
                ->with($name);
        }

        $request
            ->expects($this->at($r++))
            ->method('addDetector')
            ->with('api');

        $listener->setupDetectors();
    }

    /**
     * testSetupDetectorsIntigration
     *
     * @return void
     */
    public function testSetupDetectorsIntigration()
    {
        $detectors = [
            'json' => ['accept' => ['application/json'], 'param' => '_ext', 'value' => 'json'],
            'xml' => [
                'accept' => ['application/xml', 'text/xml'],
                'exclude' => ['text/html'],
                'param' => '_ext',
                'value' => 'xml',
            ],
            'jsonapi' => ['accept' => ['application/vnd.api+json']],
        ];

        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_request', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['_acceptHeaderDetector'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $listener
            ->expects($this->at($i++))
            ->method('_request')
            ->will($this->returnValue($request));
        $listener
            ->expects($this->at($i++))
            ->method('getConfig')
            ->with('detectors')
            ->will($this->returnValue($detectors));

        $listener->setupDetectors();

        // Test with "ext"
        foreach ($detectors as $name => $configuration) {
            if (!isset($configuration['value'])) {
                continue;
            }
            $request = $request->withParam('_ext', $configuration['value']);
            $request->clearDetectorCache();
            if ($configuration['value'] !== false) {
                $this->assertTrue($request->is($name));
            }
        }

        $request = $request->withParam('_ext', null);
        $request->clearDetectorCache();

        // Test with "accepts"
        $r = 0;
        foreach ($detectors as $name => $configuration) {
            $request
                ->expects($this->at($r++))
                ->method('_acceptHeaderDetector')
                ->with($configuration)
                ->will($this->returnValue(true));
        }

        foreach ($detectors as $name => $config) {
            $request->clearDetectorCache();
            $this->assertTrue($request->is($name));
        }

        $request = $request->withParam('_ext', 'xml');
        $request->clearDetectorCache();

        $this->assertTrue(
            $request->is('api'),
            'A request with xml extensions should be considered an api request'
        );

        $request = $request->withParam('_ext', null);
        $request->clearDetectorCache();

        $this->assertFalse(
            $request->is('api'),
            'A request with no extensions should not be considered an api request'
        );

        //Ensure that no set extension will not result in a true
        $request->clearDetectorCache();
        $request->expects($this->any())
            ->method('_acceptHeaderDetector')
            ->will($this->returnValue(false));

        $this->assertFalse($request->is('jsonapi'), 'A request with no extensions should not be considered an jsonapi request');
    }

    /**
     * data provider for testCheckRequestMethods
     *
     * @return array
     */
    public function dataCheckRequestMethods()
    {
        return [
            'defaults' => [
                [],
                null,
                [],
            ],
            'valid get' => [
                ['methods' => ['get']],
                null,
                ['get' => true],
            ],
            'invalid post' => [
                ['methods' => ['post']],
                'Cake\Http\Exception\MethodNotAllowedException',
                ['post' => false],
            ],
            'valid put' => [
                ['methods' => ['post', 'get', 'put']],
                null,
                ['post' => false, 'get' => false, 'put' => true],
            ],
        ];
    }

    /**
     * testCheckRequestMethods
     *
     * @dataProvider dataCheckRequestMethods
     * @return void
     */
    public function testCheckRequestMethods($apiConfig, $exception, $requestMethods)
    {
        $listener = $this
            ->getMockBuilder(ApiListener::class)
            ->onlyMethods(['_action', '_request'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder(IndexAction::class)
            ->onlyMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['is'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener
            ->expects($this->at(0))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('getConfig')
            ->with('api')
            ->will($this->returnValue($apiConfig));

        if (!empty($apiConfig['methods'])) {
            $listener
                ->expects($this->at(1))
                ->method('_request')
                ->will($this->returnValue($request));

            $r = 0;
            foreach ($requestMethods as $method => $bool) {
                $request
                    ->expects($this->at($r++))
                    ->method('is')
                    ->with($method)
                    ->will($this->returnValue($bool));
            }
        } else {
            $listener
                ->expects($this->never())
                ->method('_request');
        }

        if (is_string($exception)) {
            $this->expectException($exception);
        }

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkRequestMethods', [], $listener);
    }

    /**
     * testViewClass
     *
     * Test that both set and get works
     *
     * @return void
     */
    public function testViewClass()
    {
        $apiListener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $result = $apiListener->viewClass('json', 'Sample.ViewClass');
        $this->assertEquals($apiListener, $result, 'Setting a viewClass did not return the listener itself');

        $result = $apiListener->viewClass('json');
        $this->assertEquals('Sample.ViewClass', $result, 'The changed viewClass was not returned');
    }

    /**
     * testViewClassDefaults
     *
     * Test that the default viewClasses are as expected
     *
     * @return void
     */
    public function testViewClassDefaults()
    {
        $apiListener = $this
            ->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $result = $apiListener->getConfig('viewClasses');
        $expected = [
            'json' => 'Json',
            'xml' => 'Xml',
        ];
        $this->assertEquals($expected, $result, 'The default viewClasses setting has changed');
    }

    /**
     * testInjectViewClasses
     *
     * @return void
     */
    public function testInjectViewClasses()
    {
        $controller = $this
            ->getMockBuilder(Controller::class)
            ->addMethods(['foobar'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->RequestHandler = $this->getMockBuilder(RequestHandlerComponent::class)
            ->onlyMethods(['setConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $controller->RequestHandler
            ->expects($this->at(0))
            ->method('setConfig')
            ->with('viewClassMap', ['json' => 'Json']);
        $controller->RequestHandler
            ->expects($this->at(1))
            ->method('setConfig')
            ->with('viewClassMap', ['xml' => 'Xml']);

        $apiListener = $this->getMockBuilder(ApiListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_controller'])
            ->getMock();

        $apiListener
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $apiListener->injectViewClasses();
    }
}
