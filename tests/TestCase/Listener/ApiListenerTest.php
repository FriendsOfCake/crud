<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Core\Configure;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiListenerTest extends TestCase {

/**
 * Test implementedEvents with API request
 *
 * @return void
 */
	public function testImplementedEvents() {
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
	public function testImplementedEventsWithoutApi() {
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
	public function testSetup() {
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
	public function testBeforeHandle() {
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
	public function testResponse() {
		$action = $this
			->getMockBuilder('\Crud\Action\IndexAction')
			->disableOriginalConstructor()
			->setMethods(['config'])
			->getMock();

		$response = $this
			->getMockBuilder('\Cake\Network\Response')
			->setMethods(['statusCode'])
			->getMock();

		$subject = $this->getMock('\Crud\Event\Subject');
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
	public function testResponseWithExceptionConfig() {
		$action = $this
			->getMockBuilder('\Crud\Action\IndexAction')
			->disableOriginalConstructor()
			->setMethods(['config'])
			->getMock();

		$subject = $this->getMock('\Crud\Event\Subject');
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
	public function testDefaultConfiguration() {
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
			]
		];
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
				'\Cake\Network\Exception\BadRequestException',
				'Unknown error',
				0
			),

			'change exception class' => array(
				array('class' => '\Cake\Core\Exception\Exception'),
				'\Cake\Core\Exception\Exception',
				'Unknown error',
				0
			),

			'change exception code' => array(
				array('code' => 10),
				'\Cake\Network\Exception\BadRequestException',
				'Unknown error',
				10
			),

			'change exception message' => array(
				array('message' => 'epic message'),
				'\Cake\Network\Exception\BadRequestException',
				'epic message',
				0
			),

			'Validate case #1 - no validation errors' => array(
				array('class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'),
				'\Crud\Error\Exception\ValidationException',
				'0 validation errors occurred',
				412
			),

			'Validate case #2 - one validation error' => array(
				array('class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'),
				'\Crud\Error\Exception\ValidationException',
				'A validation error occurred',
				412,
				array(array('id' => 'hello world'))
			),

			'Validate case #3 - two validation errors' => array(
				array('class' => '\Crud\Error\Exception\ValidationException', 'type' => 'validate'),
				'\Crud\Error\Exception\ValidationException',
				'2 validation errors occurred',
				412,
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
	public function test_exceptionResponse($apiConfig, $exceptionClass, $exceptionMessage, $exceptionCode, $validationErrors = []) {
		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$event = new \Cake\Event\Event('Crud.Exception', new \Crud\Event\Subject());

		if (isset($apiConfig['type']) && $apiConfig['type'] === 'validate') {

			$event->subject->set([
				'entity' => $this->getMock('\Cake\ORM\Entity', ['errors'])
			]);

			$event->subject->entity
				->expects($this->any())
				->method('errors')
				->will($this->returnValue($validationErrors));

		} else {
			$listener->expects($this->never())->method('_validationErrors');
		}

		$this->setExpectedException($exceptionClass, $exceptionMessage, $exceptionCode);

		$this->setReflectionClassInstance($listener);
		$this->callProtectedMethod('_exceptionResponse', [$event, $apiConfig], $listener);
	}

/**
 * test_ensureSerializeWithViewVar
 *
 * @return void
 */
	public function test_ensureSerializeWithViewVar() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('\Crud\Action\IndexAction')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Action\IndexAction')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
			->setMethods(array('_action', '_controller'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('\Crud\Action\IndexAction')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
 * test_ensureSuccessAlreadySet
 *
 * @return void
 */
	public function test_ensureSuccessAlreadySet() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
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
		$Request->addDetector('api', ['callback' => function() {
			return true;
		}]);

		$subject = new \Crud\Event\Subject(array('request' => $Request));

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
			->getMockBuilder('\Crud\Listener\ApiListener')
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
		$this->skipIf(true);

		$detectors = array('xml' => array(), 'json' => array());

		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
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
		$detectors = [
			'json' => ['ext' => 'json', 'accepts' => 'application/json'],
			'xml' => ['ext' => 'xml', 'accepts' => 'text/xml']
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
			$this->assertTrue($request->is($name));
		}

		$request->params['_ext'] = null;

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

		$request->params['_ext'] = 'xml';
		$this->assertTrue($request->is('api'));

		$request->params['_ext'] = null;
		$this->assertFalse($request->is('api'));
	}

/**
 * testRegisterExceptionHandler with Api request
 *
 * @return void
 */
	public function testRegisterExceptionHandlerWithApi() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
			->setMethods(null)
			->disableOriginalConstructor()
			->getMock();

		$listener->registerExceptionHandler();

		$expected = 'Crud\Error\ExceptionRenderer';
		$result = Configure::read('Error.exceptionRenderer');
		$this->assertEquals($expected, $result);
	}

/**
 * testRegisterExceptionHandler without Api request
 *
 * @return void
 */
	public function testRegisterExceptionHandlerWithoutApi() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\ApiListener')
			->setMethods(null)
			->disableOriginalConstructor()
			->getMock();

		$listener->registerExceptionHandler();

		$expected = 'Crud\Error\ExceptionRenderer';
		$result = Configure::read('Error.exceptionRenderer');
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
				'Cake\Network\Exception\BadRequestException',
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
			->getMockBuilder('\Crud\Listener\ApiListener')
			->setMethods(['_action', '_request'])
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('CrudAction')
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
	public function testViewClassDefaults() {
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
	public function testInjectViewClasses() {
		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(['foo'])
			->disableOriginalConstructor()
			->getMock();

		$controller->RequestHandler = $this->getMock('RequestHandler', ['viewClassMap']);
		$controller->RequestHandler
			->expects($this->at(0))
			->method('viewClassMap')
			->with('json', 'Json');
		$controller->RequestHandler
			->expects($this->at(1))
			->method('viewClassMap')
			->with('xml', 'Xml');

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
