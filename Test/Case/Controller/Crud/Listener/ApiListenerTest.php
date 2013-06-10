<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
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
}
