<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Core\Configure;
use Crud\TestSuite\TestCase;

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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['setupDetectors', '_checkRequestType'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('setupDetectors');
        $listener
            ->expects($this->next($listener))
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(true));

        $expected = [
            'Crud.beforeHandle' => ['callable' => [$listener, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$listener, 'setFlash'], 'priority' => 5],

            'Crud.beforeRender' => ['callable' => [$listener, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$listener, 'respond'], 'priority' => 100]
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['setupDetectors', '_checkRequestType'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('setupDetectors');
        $listener
            ->expects($this->next($listener))
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(false));

        $expected = [];
        $result = $listener->implementedEvents();
        $this->assertEquals($expected, $result);
    }

    /**
     * testSetup
     *
     * @return void
     */
    public function testSetup()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['registerExceptionHandler', '_checkRequestType'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(true));
        $listener
            ->expects($this->next($listener))
            ->method('registerExceptionHandler');

        $listener->setup();
    }

    /**
     * testBeforeHandle
     *
     * @return void
     */
    public function testBeforeHandle()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_checkRequestMethods'])
            ->disableOriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('_checkRequestMethods');

        $listener->beforeHandle(new \Cake\Event\Event('Crud.beforeHandle'));
    }

    /**
     * Test response method
     *
     * @return void
     */
    public function testResponse()
    {
        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();

        $response = $this
            ->getMockBuilder('\Cake\Network\Response')
            ->setMethods(['statusCode'])
            ->getMock();

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->getMock();
        $subject->success = true;

        $event = new \Cake\Event\Event('Crud.afterSave', $subject);

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_action', 'render'])
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $action
            ->expects($this->next($action))
            ->method('config')
            ->with('api.success')
            ->will($this->returnValue(['code' => 200]));
        $listener
            ->expects($this->next($listener))
            ->method('render')
            ->with($subject)
            ->will($this->returnValue($response));
        $response
            ->expects($this->next($response))
            ->method('statusCode')
            ->with(200);

        $listener->respond($event);
    }

    /**
     * Test response method with exception config
     *
     * @return void
     */
    public function testResponseWithExceptionConfig()
    {
        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();

        $subject = $this->getMockBuilder('\Crud\Event\Subject')
            ->getMock();
        $subject->success = true;

        $event = new \Cake\Event\Event('Crud.afterSave', $subject);

        $i = 0;

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_action', 'render', '_exceptionResponse'])
            ->getMock();
        $listener
            ->expects($this->next($listener))
            ->method('_action')
            ->with()
            ->will($this->returnValue($action));
        $action
            ->expects($this->next($action))
            ->method('config')
            ->with('api.success')
            ->will($this->returnValue(['exception' => 'SomethingExceptional']));
        $listener
            ->expects($this->next($listener))
            ->method('_exceptionResponse')
            ->with($event, 'SomethingExceptional');
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expected = [
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
                'class' => 'Cake\Network\Exception\BadRequestException',
                'message' => 'Unknown error',
                'code' => 0
            ],
            'exceptionRenderer' => 'Crud\Error\ExceptionRenderer',
            'setFlash' => false
        ];
        $result = $listener->config();
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
                '\Cake\Network\Exception\BadRequestException',
                'Unknown error',
                0
            ],

            'change exception class' => [
                ['class' => '\Cake\Core\Exception\Exception'],
                '\Cake\Core\Exception\Exception',
                'Unknown error',
                0
            ],

            'change exception code' => [
                ['code' => 10],
                '\Cake\Network\Exception\BadRequestException',
                'Unknown error',
                10
            ],

            'change exception message' => [
                ['message' => 'epic message'],
                '\Cake\Network\Exception\BadRequestException',
                'epic message',
                0
            ],

            'Validate case #1 - no validation errors' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                '0 validation errors occurred',
                422
            ],

            'Validate case #2 - one validation error' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                'A validation error occurred',
                422,
                [['id' => 'hello world']]
            ],

            'Validate case #3 - two validation errors' => [
                ['class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'],
                '\Crud\Error\Exception\ValidationException',
                '2 validation errors occurred',
                422,
                [['id' => 'hello world', 'name' => 'fail me']]
            ]
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $event = new \Cake\Event\Event('Crud.Exception', new \Crud\Event\Subject());

        if (isset($apiConfig['type']) && $apiConfig['type'] === 'validate') {
            $event->subject->set([
                'entity' => $this->getMockBuilder('\Cake\ORM\Entity')
                    ->setMethods(['errors'])
                    ->getMock()
            ]);

            $event->subject->entity
                ->expects($this->any())
                ->method('errors')
                ->will($this->returnValue($validationErrors));
        }

        $this->setExpectedException($exceptionClass, $exceptionMessage, $exceptionCode);

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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->setMethods(['config', 'viewVar'])
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
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('_serialize', ['success', 'data' => 'items']);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);
    }

    /**
     * testEnsureSerializeAlreadySet
     *
     * @return void
     */
    public function testEnsureSerializeAlreadySet()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->viewVars['_serialize'] = 'hello world';

        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->setMethods(['config', 'viewVar'])
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
    }

    /**
     * testEnsureSerializeWithViewVarChanged
     *
     * @return void
     */
    public function testEnsureSerializeWithViewVarChanged()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->setMethods(['config', 'viewVar'])
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
        $controller
            ->expects($this->once())
            ->method('set')
            ->with('_serialize', ['success', 'data' => 'helloWorld']);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);
    }

    /**
     * testEnsureSerializeWithoutViewVar
     *
     * @return void
     */
    public function testEnsureSerializeWithoutViewVar()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_action', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\AddAction')
            ->setMethods(['config', 'scope', '_controller'])
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
            ->expects($this->once())
            ->method('set')
            ->with('_serialize', ['success', 'data' => '']);
        $action->expects($this->any())
            ->method('scope')
            ->will($this->returnValue('table'));
        $action->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_ensureSerialize', [], $listener);
    }

    /**
     * testEnsureSuccess
     *
     * @return void
     */
    public function testEnsureSuccess()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => true]);

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => true]);

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
            ->method('config')
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => true, 'id' => 1, 'modelClass' => 'MyModel']);

        $config = ['data' => [
            'subject' => [
                '{modelClass}.id' => 'id',
                'modelClass'
            ]
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
            ->method('config')
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => true, 'id' => 1, 'modelClass' => 'MyModel']);

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
            ->method('config')
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller', '_action'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => false]);

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
            ->method('config')
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject(['success' => true]);

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->viewVars['success'] = true;

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
        $Request = new \Cake\Network\Request();
        $Request->addDetector('api', ['callback' => function () {
            return true;
        }]);

        $subject = new \Crud\Event\Subject(['request' => $Request]);

        $apiListener = $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Cake\Event\Event('Crud.setFlash', $subject);
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
        $Request = new \Cake\Network\Request();
        $Request->addDetector('api', ['callback' => function () {
            return true;
        }]);

        $subject = new \Crud\Event\Subject(['request' => $Request]);

        $apiListener = $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $event = new \Cake\Event\Event('Crud.setFlash', $subject);
        $apiListener->config(['setFlash' => true]);
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
                new \Crud\Event\Subject(['modelClass' => 'MyModel']),
                '{modelClass}.id',
                'MyModel.id'
            ],

            'string and integer' => [
                new \Crud\Event\Subject(['modelClass' => 'MyModel', 'id' => 1]),
                '{modelClass}.{id}',
                'MyModel.1'
            ],

            'ignore non scalar' => [
                new \Crud\Event\Subject(['modelClass' => 'MyModel', 'complex' => new \StdClass]),
                '{modelClass}.{id}',
                'MyModel.{id}'
            ]
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_request', 'config'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(['addDetector'])
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
            'json' => ['ext' => 'json', 'accepts' => 'application/json'],
            'xml' => ['ext' => 'xml', 'accepts' => 'text/xml'],
            'jsonapi' => ['ext' => false, 'accepts' => 'application/vnd.api+json']
        ];

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_request', 'config'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(['accepts'])
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

        $listener->setupDetectors();

        // Test with "ext"
        foreach ($detectors as $name => $configuration) {
            $request->params['_ext'] = $configuration['ext'];
            $request->clearDetectorCache();
            if ($configuration['ext'] !== false) {
                $this->assertTrue($request->is($name));
            }
        }

        $request->params['_ext'] = null;
        $request->clearDetectorCache();

        // Test with "accepts"
        $r = 0;
        foreach ($detectors as $name => $configuration) {
            $request
                ->expects($this->at($r++))
                ->method('accepts')
                ->with($configuration['accepts'])
                ->will($this->returnValue(true));
        }

        foreach ($detectors as $name => $config) {
            $request->clearDetectorCache();
            $this->assertTrue($request->is($name));
        }

        $request->params['_ext'] = 'xml';
        $request->clearDetectorCache();

        $this->assertTrue(
            $request->is('api'),
            "A request with xml extensions should be considered an api request"
        );

        $request->params['_ext'] = null;
        $request->clearDetectorCache();

        $this->assertFalse(
            $request->is('api'),
            "A request with no extensions should not be considered an api request"
        );

        //Ensure that no set extension will not result in a true
        unset($request->params['_ext']);
        $request->clearDetectorCache();
        $request->expects($this->any())
            ->method('accepts')
            ->will($this->returnValue(false));

        $this->assertFalse($request->is('jsonapi'), "A request with no extensions should not be considered an jsonapi request");
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
                false,
                []
            ],
            'valid get' => [
                ['methods' => ['get']],
                true,
                ['get' => true]
            ],
            'invalid post' => [
                ['methods' => ['post']],
                'Cake\Network\Exception\BadRequestException',
                ['post' => false]
            ],
            'valid put' => [
                ['methods' => ['post', 'get', 'put']],
                true,
                ['post' => false, 'get' => false, 'put' => true]
            ]
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->setMethods(['_action', '_request'])
            ->disableOriginalConstructor()
            ->getMock();

        $action = $this
            ->getMockBuilder('\Crud\Action\IndexAction')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(['is'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener
            ->expects($this->at(0))
            ->method('_action')
            ->will($this->returnValue($action));
        $action
            ->expects($this->at(0))
            ->method('config')
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
            $this->setExpectedException($exception);
        }

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_checkRequestMethods', [], $listener);

        if (is_bool($exception)) {
            $this->assertEquals($exception, $result);
        }
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
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
            ->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $apiListener->config('viewClasses');
        $expected = [
            'json' => 'Json',
            'xml' => 'Xml'
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
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['foobar'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->RequestHandler = $this->getMockBuilder('\Cake\Controller\Component\RequestHandlerComponent')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();
        $controller->RequestHandler
            ->expects($this->at(0))
            ->method('config')
            ->with('viewClassMap', ['json' => 'Json']);
        $controller->RequestHandler
            ->expects($this->at(1))
            ->method('config')
            ->with('viewClassMap', ['xml' => 'Xml']);

        $apiListener = $this->getMockBuilder('\Crud\Listener\ApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller'])
            ->getMock();

        $apiListener
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $apiListener->injectViewClasses();
    }
}
