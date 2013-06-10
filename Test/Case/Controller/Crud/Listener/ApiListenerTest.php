<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('ApiListener', 'Crud.Controller/Crud/Listener');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ApiListenerTest extends CakeTestCase {

	protected $config;

	public function setUp() {
		parent::setUp();
		$this->config = Configure::read();
	}

	public function tearDown() {
		parent::tearDown();
		Configure::write($this->config);
	}

	public function testInitNotIsAPi() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts'));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$apiListener->init($event);

		//Testing detectors
		$subject->request->expects($this->at(0))
			->method('accepts')
			->with('application/json')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('accepts')
			->with('application/json')
			->will($this->returnValue(false));

		$subject->request->expects($this->at(2))
			->method('accepts')
			->with('text/xml')
			->will($this->returnValue(false));

		$subject->request->expects($this->at(3))
			->method('accepts')
			->with('text/xml')
			->will($this->returnValue(true));

		$this->assertTrue($subject->request->is('json'));
		$this->assertFalse($subject->request->is('json'));

		$subject->request->params['ext'] = 'json';
		$this->assertTrue($subject->request->is('json'));
		$this->assertTrue($subject->request->is('api'));

		
		$this->assertFalse($subject->request->is('xml'));
		$this->assertTrue($subject->request->is('xml'));

		$subject->request->params['ext'] = 'xml';
		$this->assertTrue($subject->request->is('xml'));
		$this->assertTrue($subject->request->is('api'));
	}

/**
 * Returns a list of actions and their http method
 *
 * @return void
 */
	public function actionsProvider() {
		return array(
			array('index', 'get'),
			array('view', 'get'),
			array('admin_index', 'get'),
			array('admin_view', 'get'),
			array('add', 'post'),
			array('admin_add', 'post'),
			array('edit', 'put'),
			array('admin_edit', 'put'),
			array('delete', 'delete'),
			array('admin_delete', 'delete')
		);
	}

/**
 * Tests initialization and expect no error
 *
 * @dataProvider actionsProvider
 * @return void
 */
	public function testIniIsAPI($action, $method) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->response = $this->getMock('CakeResponse');
		$subject->action = $subject->request->action = $action;

		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$subject->request->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('is')
			->with($method)
			->will($this->returnValue(true));
		$apiListener->init($event);
		$this->assertEquals('Crud.CrudExceptionRenderer', Configure::read('Exception.renderer'));
		$this->assertTrue(class_exists('CrudExceptionRenderer'));
	}

/**
 * Tests initialization with worng match of action and method
 *
 * @dataProvider actionsProvider
 * @expectedException MethodNotAllowedException
 * @return void
 */
	public function testIniError($action, $method) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->response = $this->getMock('CakeResponse');
		$subject->action = $subject->request->action = $action;

		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$subject->request->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('is')
			->with($method)
			->will($this->returnValue(false));
		$apiListener->init($event);
	}

/**
 * Tests that the function is not run if it is not an API call
 *
 * @return void
 */
	public function testAfterSaveNotAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));
		$subject->controller->expects($this->never())->method('set');
		$event = new CakeEvent('Crud.init', $subject);
		$apiListener->afterSave($event);
	}

/**
 * Returns the 2 possible states for subject->created
 *
 * @return void
 */
	public function createdProvider() {
		return array(
			array(true, 'once'),
			array(false, 'never')
		);
	}

/**
 * Test the response mangling after saving a record and is an API call
 *
 * @dataProvider createdProvider
 * @return void
 */
	public function testAfterSaveSuccess($created, $matcher) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = true;
		$subject->controller->expects($this->at(0))
			->method('set')
			->with('success', true);

		$subject->model = new Model(array('alias' => 'Thing'));
		$subject->id = 100;
		$subject->controller->expects($this->at(1))
			->method('set')
			->with('data', array('Thing' => array('id' => 100)));

		$subject->controller->expects($this->once())->method('render')
			->will($this->returnValue($subject->response));

		$subject->created = $created;
		$expect = $subject->response->expects($this->{$matcher}())->method('statusCode');

		if ($created) {
			$expect->with(201);
		}

		$subject->response->expects($this->once())->method('header')
			->with('Location', Router::url(array('action' => 'view', 100), true));

		$event = new CakeEvent('Crud.afterSave', $subject);
		$result = $apiListener->afterSave($event);
		$this->assertSame($subject->response, $result);
	}

/**
 * Tests afterSave method when some validation errors occurred
 *
 * @return void
 */
	public function testAfterSaveNotSuccess() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = false;
		$subject->controller->expects($this->at(0))
			->method('set')
			->with('success', false);

		$subject->response->expects($this->once())->method('statusCode')->with(400);

		$subject->model = new Model(array('alias' => 'Thing'));
		$subject->model->validationErrors = array('field' => 'An error');
		$subject->controller->expects($this->at(1))
			->method('set')
			->with('data', $subject->model->validationErrors);

		$subject->controller->expects($this->never())->method('render');
		$event = new CakeEvent('Crud.afterSave', $subject);
		$this->assertNull($apiListener->afterSave($event));
	}

/**
 * Tests that afterDelete logic is not run if the call is not API
 *
 * @return void
 */
	public function testAfterDeleteNotAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));
		$subject->controller->expects($this->never())->method('set');
		$event = new CakeEvent('Crud.afterDelete', $subject);
		$apiListener->afterDelete($event);
	}

/**
 * Tests afterDelete logic
 *
 * @return void
 */
	public function testAfterDelete() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = true;
		$subject->controller->expects($this->at(0))->method('set')
			->with('success', true);

		$subject->controller->expects($this->at(1))->method('set')
			->with('data', null);

		$subject->controller->expects($this->once())
			->method('render')
			->will($this->returnValue($subject->response));

		$event = new CakeEvent('Crud.afterDelete', $subject);
		$this->assertEquals($subject->response, $apiListener->afterDelete($event));
	}

/**
 * Tests that recordNotFound will do nothing when the call is not API
 *
 * @return void
 */
	public function testRecordNotFoundNoAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$apiListener = new ApiListener($subject);
		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));

		$event = new CakeEvent('Crud.recordNotFound', $subject);
		$apiListener->recordNotFound($event);
	}

/**
 * Tests that recordNotFound will throw an exception
 *
 * @expectedException NotFoundException
 * @return void
 */
	public function testRecordNotFound() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$apiListener = new ApiListener($subject);
		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$event = new CakeEvent('Crud.recordNotFound', $subject);
		$apiListener->recordNotFound($event);
	}

	/**
 * Tests that invalidId will do nothing when the call is not API
 *
 * @return void
 */
	public function testInvalidIdNoAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$apiListener = new ApiListener($subject);
		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));

		$event = new CakeEvent('Crud.invalidId', $subject);
		$apiListener->invalidId($event);
	}

/**
 * Tests that recordNotFound will throw an exception
 *
 * @expectedException BadRequestException
 * @return void
 */
	public function testInvalidID() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$apiListener = new ApiListener($subject);
		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$event = new CakeEvent('Crud.invalidId', $subject);
		$apiListener->invalidId($event);
	}

	
}
