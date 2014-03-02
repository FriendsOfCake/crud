<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Core\Configure;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiTest extends TestCase {

	protected $_config;

	public function setUp() {
		parent::setUp();
		$this->_config = Configure::read();
	}

	public function tearDown() {
		parent::tearDown();
		Configure::write($this->_config);
	}

/**
 * Test implementedEvents with API request
 *
 * @covers \Crud\Listener\Api::implementedEvents
 * @return void
 */
	public function testImplementedEvents() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(['setupDetectors', '_request'])
			->disableOriginalConstructor()
			->getMock();
		$request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->next($listener))
			->method('setupDetectors');
		$listener
			->expects($this->next($listener))
			->method('_request')
			->will($this->returnValue($request));
		$request
			->expects($this->next($request))
			->method('is')
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
 * @covers \Crud\Listener\Api::implementedEvents
 * @return void
 */
	public function testImplementedEventsWithoutApi() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(['setupDetectors', '_request'])
			->disableOriginalConstructor()
			->getMock();
		$request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->next($listener))
			->method('setupDetectors');
		$listener
			->expects($this->next($listener))
			->method('_request')
			->will($this->returnValue($request));
		$request
			->expects($this->next($request))
			->method('is')
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
	public function testSetup() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(['registerExceptionHandler'])
			->disableOriginalConstructor()
			->getMock();

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
	public function testBeforeHandle() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
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
	public function testResponse() {
		$request = $this->getMock('\Cake\Network\Request', array('is'));
		$response = $this->getMock('\Cake\Network\Response');

		$action = $this->getMock('\Crud\Action\Index', array('config'), array(new \Crud\Event\Subject()));

		$subject = $this->getMock('\Crud\Event\Subject');
		$subject->success = true;

		$event = new CakeEvent('Crud.afterSave', $subject);

		$i = 0;

		$listener = $this->getMock('\Crud\Listener\Api', array('_request', '_action', 'render'), array($subject));
		$listener
			->expects($this->at($i++))
			->method('_action')
			->with()
			->will($this->returnValue($action));
		$action
			->expects($this->at(0))
			->method('config')
			->with('api.success')
			->will($this->returnValue(array('code' => 200)));
		$listener
			->expects($this->at($i++))
			->method('render')
			->with($subject)
			->will($this->returnValue($response));
		$response
			->expects($this->at(0))
			->method('statusCode')
			->with(200);

		$result = $listener->respond($event);
		$this->assertSame($response, $result);
	}

/**
 * Test response method with exception config
 *
 * @return void
 */
	public function testResponseWithExceptionConfig() {
		$request = $this->getMock('\Cake\Network\Request', array('is'));
		$response = $this->getMock('\Cake\Network\Response');

		$action = $this->getMock('\Crud\Action\Index', array('config'), array(new \Crud\Event\Subject()));

		$subject = $this->getMock('\Crud\Event\Subject');
		$subject->success = true;

		$event = new CakeEvent('Crud.afterSave', $subject);

		$i = 0;

		$listener = $this->getMock('\Crud\Listener\Api', array('_request', '_action', 'render', '_exceptionResponse'), array($subject));
		$listener
			->expects($this->at($i++))
			->method('_action')
			->with()
			->will($this->returnValue($action));
		$action
			->expects($this->at(0))
			->method('config')
			->with('api.success')
			->will($this->returnValue(array('exception' => true)));
		$listener
			->expects($this->at($i++))
			->method('_exceptionResponse')
			->with(true);
		$listener
			->expects($this->never())
			->method('render');
		$response
			->expects($this->never())
			->method('statusCode');

		$listener->respond($event);
	}

/**
 * Test default configuration
 *
 * @return void
 */
	public function testDefaultConfiguration() {
		$listener = new \Crud\Listener\Api(new \Crud\Event\Subject());
		$expected = array(
			'viewClasses' => array(
				'json' => 'Crud.CrudJson',
				'xml' => 'Crud.CrudXml'
			),
			'detectors' => array(
				'json' => array('ext' => 'json', 'accepts' => 'application/json'),
				'xml' => array('ext' => 'xml', 'accepts' => 'text/xml')
			),
			'exception' => array(
				'type' => 'default',
				'class' => 'BadRequestException',
				'message' => 'Unknown error',
				'code' => 0
			)
		);
		$result = $listener->config();
		$this->assertEquals($expected, $result);
	}

/**
 * Data provider for test_exceptionResponse
 *
 * @return array
 */
	public function data_exceptionResponse() {
		return array(
			'default configuration' => array(
				array(),
				'\Cake\Error\BadRequestException',
				'Unknown error',
				0
			),

			'change exception class' => array(
				array('class' => '\Cake\Error\BaseException'),
				'\Cake\Error\BaseException',
				'Unknown error',
				0
			),

			'change exception code' => array(
				array('code' => 10),
				'\Cake\Error\BadRequestException',
				'Unknown error',
				10
			),

			'change exception message' => array(
				array('message' => 'epic message'),
				'\Cake\Error\BadRequestException',
				'epic message',
				10
			),

			'Validate case #1 - no validation errors' => array(
				array('class' => '\Crud\Error\CrudValidationException', 'type' => 'validate'),
				'\Crud\Error\CrudValidationException',
				'0 validation errors occurred',
				0
			),

			'Validate case #2 - one validation error' => array(
				array('class' => '\Crud\Error\CrudValidationException', 'type' => 'validate'),
				'\Crud\Error\CrudValidationException',
				'A validation error occurred',
				0,
				array(array('id' => 'hello world'))
			),

			'Validate case #3 - two validation errors' => array(
				array('class' => '\Crud\Error\CrudValidationException', 'type' => 'validate'),
				'\Crud\Error\CrudValidationException',
				'2 validation errors occurred',
				0,
				array(array('id' => 'hello world', 'name' => 'fail me'))
			)
		);
	}

/**
 * Test _exceptionResponse
 *
 * @dataProvider data_exceptionResponse
 * @param array $apiConfig
 * @param string $exceptionClass
 * @param string $exceptionMessage
 * @param integer $exceptionCode
 * @param array $validationErrors
 * @return void
 */
	public function test_exceptionResponse($apiConfig, $exceptionClass, $exceptionMessage, $exceptionCode, $validationErrors = array()) {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$event = new \Cake\Event\Event('Crud.Exception', new \Crud\Event\Subject());

		if (isset($apiConfig['type']) && $apiConfig['type'] === 'validate') {
			$event->subject->set(['entity' => $this->getMock('\Cake\ORM\Entity', ['errors'])]);
			$event->subject->entity->expects($this->any())->method('errors')->will($this->returnValue($validationErrors));
		} else {
			$listener->expects($this->never())->method('_validationErrors');
		}

		$this->expectException($exceptionClass, $exceptionMessage, $exceptionCode);

		$this->setReflectionClassInstance($listener);
		$this->callProtectedMethod('_exceptionResponse', [$apiConfig, $event], $listener);
	}

/**
 * Test render
 *
 * @return void
 */
	public function testRender() {
		$listener = $this->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('injectViewClasses', '_ensureSuccess', '_ensureData', '_ensureSerialize', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject();

		$requestHandler = $this->getMockBuilder('RequestHandlerComponent')
			->setMethods(array('renderAs'))
			->disableOriginalConstructor()
			->getMock();
		$controller = $this->getMockBuilder('Controller')
			->setMethods(array('render'))
			->disableOriginalConstructor()
			->getMock();
		$controller->RequestHandler = $requestHandler;
		$controller->RequestHandler->ext = 'json';

		$i = 0;
		$listener
			->expects($this->at($i++))
			->method('injectViewClasses')
			->with();
		$listener
			->expects($this->at($i++))
			->method('_ensureSuccess')
			->with($subject);
		$listener
			->expects($this->at($i++))
			->method('_ensureData')
			->with($subject);
		$listener
			->expects($this->at($i++))
			->method('_ensureSerialize')
			->with();
		$listener
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($controller));
		$requestHandler
			->expects($this->once())
			->method('renderAs')
			->with($controller, 'json');
		$controller
			->expects($this->once())
			->method('render')
			->with();

		$listener->render($subject);
	}

/**
 * test_ensureSerializeWithViewVar
 *
 * @return void
 */
	public function test_ensureSerializeWithViewVar() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('\Crud\Action\Index')
			->setMethods(array('config', 'viewVar'))
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
			->with('_serialize', array('success', 'data' => 'items'));

		$this->setReflectionClassInstance($listener);
		$this->callProtectedMethod('_ensureSerialize', array(), $listener);
	}

/**
 * test_ensureSerializeAlreadySet
 *
 * @return void
 */
	public function test_ensureSerializeAlreadySet() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$controller->viewVars['_serialize'] = 'hello world';

		$action = $this
			->getMockBuilder('\Crud\Action\Index')
			->setMethods(array('config', 'viewVar'))
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
		$this->callProtectedMethod('_ensureSerialize', array(), $listener);
	}

/**
 * test_ensureSerializeWithViewVarChanged
 *
 * @return void
 */
	public function test_ensureSerializeWithViewVarChanged() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('\Crud\Action\Index')
			->setMethods(array('config', 'viewVar'))
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
			->with('_serialize', array('success', 'data' => 'helloWorld'));

		$this->setReflectionClassInstance($listener);
		$this->callProtectedMethod('_ensureSerialize', array(), $listener);
	}

/**
 * test_ensureSerializeWithoutViewVar
 *
 * @return void
 */
	public function test_ensureSerializeWithoutViewVar() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('AddCrudAction')
			->setMethods(array('config'))
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
			->with('_serialize', array('success', 'data'));

		$this->setReflectionClassInstance($listener);
		$this->callProtectedMethod('_ensureSerialize', array(), $listener);
	}

/**
 * test_ensureSuccess
 *
 * @return void
 */
	public function test_ensureSuccess() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => true));

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
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
		$this->callProtectedMethod('_ensureSuccess', array($subject), $listener);
	}

/**
 * test_ensureData
 *
 * @return void
 */
	public function test_ensureData() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller', '_action'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => true));

		$config = array();

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
			->with('data', array());

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_ensureData', array($subject), $listener);
	}

/**
 * test_ensureDataSubject
 *
 * @return void
 */
	public function test_ensureDataSubject() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller', '_action'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => true, 'id' => 1, 'modelClass' => 'MyModel'));

		$config = array('data' => array(
			'subject' => array(
				'{modelClass}.id' => 'id',
				'modelClass'
			)
		));

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
			->with('data', array('modelClass' => 'MyModel', 'MyModel' => array('id' => 1)));

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_ensureData', array($subject), $listener);
	}

/**
 * test_ensureDataRaw
 *
 * @return void
 */
	public function test_ensureDataRaw() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller', '_action'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => true, 'id' => 1, 'modelClass' => 'MyModel'));

		$config = array('data' => array('raw' => array('{modelClass}.id' => 1)));

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
			->with('data', array('MyModel' => array('id' => 1)));

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_ensureData', array($subject), $listener);
	}

/**
 * test_ensureDataError
 *
 * @return void
 */
	public function test_ensureDataError() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller', '_action'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => false));

		$config = array();

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
			->with('data', array());

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_ensureData', array($subject), $listener);
	}

/**
 * test_ensureDataExists
 *
 * @return void
 */
	public function test_ensureDataExists() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller', '_action'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$controller->viewVars['data'] = true;

		$subject = new \Crud\Event\Subject();

		$config = array();

		$i = 0;
		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));
		$listener
			->expects($this->never())
			->method('_action');
		$controller
			->expects($this->never())
			->method('set');

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_ensureData', array($subject), $listener);
	}

/**
 * test_ensureSuccessAlreadySet
 *
 * @return void
 */
	public function test_ensureSuccessAlreadySet() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_controller'))
			->disableOriginalConstructor()
			->getMock();

		$subject = new \Crud\Event\Subject(array('success' => true));

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
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
		$this->callProtectedMethod('_ensureSuccess', array($subject), $listener);
	}

/**
 * testFlashMessageSupressed
 *
 * The API listener should suppress flash messages
 * if the request is "API"
 *
 * @return void
 */
	public function testFlashMessageSupressed() {
		$Request = new \Cake\Network\Request();
		$Request->addDetector('api', array('callback' => function() {
			return true;
		}));

		$subject = new \Crud\Event\Subject(array('request' => $Request));

		$apiListener = new \Crud\Listener\Api($subject);

		$event = new CakeEvent('Crud.setFlash', $subject);
		$apiListener->setFlash($event);

		$stopped = $event->isStopped();
		$this->assertTrue($stopped, 'Set flash event is expected to be stopped');
	}

/**
 * Data provider for test_expandPath
 *
 * @return array
 */
	public function data_expandPath() {
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
 * test_expandPath
 *
 * @dataProvider data_expandPath
 * @return void
 */
	public function test_expandPath($subject, $path, $expected) {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_expandPath', array($subject, $path), $listener);
		$this->assertSame($expected, $result);
	}

/**
 * testSetupDetectors
 *
 * @return void
 */
	public function testSetupDetectors() {
		$detectors = array('xml' => array(), 'json' => array());

		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_request', 'config'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(array('addDetector'))
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
	public function testSetupDetectorsIntigration() {
		$detectors = array(
			'json' => array('ext' => 'json', 'accepts' => 'application/json'),
			'xml' => array('ext' => 'xml', 'accepts' => 'text/xml')
		);

		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_request', 'config'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(array('accepts'))
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
			$request->params['ext'] = $configuration['ext'];
			$this->assertTrue($request->is($name));
		}

		$request->params['ext'] = null;

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
			$this->assertTrue($request->is($name));
		}

		$request->params['ext'] = 'xml';
		$this->assertTrue($request->is('api'));

		$request->params['ext'] = null;
		$this->assertFalse($request->is('api'));
	}

/**
 * testRegisterExceptionHandler with Api request
 *
 * @return void
 */
	public function testRegisterExceptionHandlerWithApi() {
		$listener = $this->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_request'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this->getMockBuilder('\Cake\Network\Request')
			->setMethods(array('is'))
			->disableOriginalConstructor()
			->getMock();
		$request
			->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$listener
			->expects($this->once())
			->method('_request')
			->with()
			->will($this->returnValue($request));

		$listener->registerExceptionHandler();

		$expected = 'Crud.CrudExceptionRenderer';
		$result = Configure::read('Exception.renderer');
		$this->assertEquals($expected, $result);
	}


/**
 * testRegisterExceptionHandler without Api request
 *
 * @return void
 */
	public function testRegisterExceptionHandlerWithoutApi() {
		$listener = $this->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_request'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this->getMockBuilder('\Cake\Network\Request')
			->setMethods(array('is'))
			->disableOriginalConstructor()
			->getMock();
		$request
			->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(false));

		$listener
			->expects($this->once())
			->method('_request')
			->with()
			->will($this->returnValue($request));

		$listener->registerExceptionHandler();

		$expected = 'ExceptionRenderer';
		$result = Configure::read('Exception.renderer');
		$this->assertEquals($expected, $result);
	}
/**
 * data provider for test_checkRequestMethods
 *
 * @return array
 */
	public function data_checkRequestMethods() {
		return array(
			'defaults' => array(
				array(),
				false,
				array()
			),
			'valid get' => array(
				array('methods' => array('get')),
				true,
				array('get' => true)
			),
			'invalid post' => array(
				array('methods' => array('post')),
				'BadRequestException',
				array('post' => false)
			),
			'valid put' => array(
				array('methods' => array('post', 'get', 'put')),
				true,
				array('post' => false, 'get' => false, 'put' => true)
			)
		);
	}

/**
 * test_checkRequestMethods
 *
 * @dataProvider data_checkRequestMethods
 * @return void
 */
	public function test_checkRequestMethods($apiConfig, $exception, $requestMethods) {
		$listener = $this
			->getMockBuilder('\Crud\Listener\Api')
			->setMethods(array('_action', '_request'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(array('is'))
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
			$this->expectException($exception);
		}

		$this->setReflectionClassInstance($listener);
		$result = $this->callProtectedMethod('_checkRequestMethods', array(), $listener);

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
	public function testViewClass() {
		$apiListener = $this
			->getMockBuilder('\Crud\Listener\Api')
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
	public function testViewClassDefaults() {
		$apiListener = $this
			->getMockBuilder('\Crud\Listener\Api')
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
	public function testInjectViewClasses() {
		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('foo')) // need to mock *something* to make Controller::__set work
			->disableOriginalConstructor()
			->getMock();

		$controller->RequestHandler = $this->getMock('RequestHandler', array('viewClassMap'));
		$controller->RequestHandler->expects($this->at(0))->method('viewClassMap')->with('json', 'Crud.CrudJson');
		$controller->RequestHandler->expects($this->at(1))->method('viewClassMap')->with('xml', 'Crud.CrudXml');

		$apiListener = $this->getMock('\Crud\Listener\Api', array('_controller'), array(new \Crud\Event\Subject()));
		$apiListener->expects($this->once())->method('_controller')->will($this->returnValue($controller));
		$apiListener->injectViewClasses();
	}

}
