<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('AddCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudComponent', 'Crud.Controlller/Component');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class AddCrudActionTest extends CakeTestCase {

	protected $ModelMock;

	protected $ControllerMock;

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ControllerMock = $this->getMockBuilder('Controller');
		$this->ActionMock = $this->getMockBuilder('AddCrudAction');
		$this->RequestMock = $this->getMockBuilder('CakeRequest');
		$this->CrudMock = $this->getMockBuilder('CrudComponent');
	}

	public function tearDown() {
		parent::tearDown();

		unset(
			$this->ModelMock,
			$this->ControllerMock,
			$this->ActionMock,
			$this->RequestMock,
			$this->CrudMock
		);
	}

/**
 * Returns a list of mocked classes that are related to the execution of the
 * action
 *
 * @return void
 */
	protected function _mockClasses() {
		$CrudSubject = new CrudSubject();

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'saveAll'))
			->getMock();

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => $Controller,
			'handleAction' => 'add',
			'action' => 'add',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', '_redirect'))
			->getMock();

		return compact('Crud', 'Model', 'Controller', 'Request', 'CrudSubject', 'Action');
	}

/**
 * Test that calling handle will invoke _handle
 *
 * @return void
 */
	public function testThatCrudActionWillHandle() {
		extract($this->_mockClasses());

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', '_redirect', '_handle'))
			->getMock();

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));
		$Action
			->expects($this->once())
			->method('_handle');

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an add action
 * will only trigger beforeRender()
 *
 * @return void
 */
	public function testActionGet() {
		extract($this->_mockClasses());

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));

		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeRender', array('success' => false));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on success
 *
 * @return void
 */
	public function testActionPostSuccess() {
		extract($this->_mockClasses());

		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model->id = 1;
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(true));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => true, 'created' => true, 'id' => $Model->id))
			->will($this->returnValue($CrudSubject));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));
		$Action
			->expects($this->once())
			->method('setFlash')
			->with('create.success');
		$Action
			->expects($this->once())
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on error
 *
 * @return void
 */
	public function testActionPostError() {
		extract($this->_mockClasses());

		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model->id = null;
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');

		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => false, 'created' => false))
			->will($this->returnValue($CrudSubject));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));

		$Action
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));

		$Action
			->expects($this->once())
			->method('setFlash')
			->with('create.error');

		$Action
			->expects($this->never())
			->method('_redirect');

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on error and merge
 * the model data with the post data
 *
 * @return void
 */
	public function testActionPostErrorAndMergeData() {
		extract($this->_mockClasses());

		$Request->data = array('request_data' => true);
		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model->id = null;
		$Model->data = array('model_data' => true);
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => false, 'created' => false))
			->will($this->returnValue($CrudSubject));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));
		$Action
			->expects($this->once())
			->method('setFlash')
			->with('create.error');
		$Action
			->expects($this->never())
			->method('_redirect');

		$Action->handle($CrudSubject);

		$expects = array('request_data' => true, 'model_data' => true);
		$actual = $Request->data;
		$this->assertSame($expects, $actual, 'The request and model data was not merged');
	}

}
