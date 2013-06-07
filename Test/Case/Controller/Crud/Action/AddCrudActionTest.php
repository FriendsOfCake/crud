<?php

App::uses('Model', 'Model');
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

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ActionMock = $this->getMockBuilder('AddCrudAction');
		$this->RequestMock = $this->getMockBuilder('CakeRequest');
		$this->CrudMock = $this->getMockBuilder('CrudComponent');
	}

	public function tearDown() {
		parent::tearDown();

		unset(
			$this->ModelMock,
			$this->ActionMock,
			$this->RequestMock,
			$this->CrudMock
		);
	}

/**
 * Test that calling handle will invoke _handle
 *
 * @return void
 */
	public function testThatCrudActionWillHandle() {
		$Action = $this->ActionMock
			->disableOriginalConstructor()
			->setMethods(array('config', '_handle'))
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

		$CrudSubject = new CrudSubject(array(
			'action' => 'add',
			'model' => new StdClass(),
			'modelClass' => 'Blog',
			'args' => array()
		));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an add action
 * will only trigger beforeRender()
 *
 * @return void
 */
	public function testActionGet() {
		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(false));

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeRender', array('success' => false));

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => new Controller,
			'handleAction' => 'add',
			'action' => 'add',
			'model' => null,
			'modelClass' => null,
			'args' => array()
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config'))
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

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on success
 *
 * @return void
 */
	public function testActionPostSuccess() {
		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('saveAll'))
			->getMock();
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(true));
		$Model->id = 1;

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => new Controller,
			'handleAction' => 'add',
			'action' => 'add',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array()
		));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => true, 'created' => true, 'id' => $Model->id))
			->will($this->returnValue($CrudSubject));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', 'setFlash', '_redirect'))
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
		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('saveAll'))
			->getMock();
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(false));
		$Model->id = null;

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => new Controller,
			'handleAction' => 'add',
			'action' => 'add',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array()
		));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => false, 'created' => false))
			->will($this->returnValue($CrudSubject));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', 'setFlash', '_redirect'))
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
		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->at(0))
			->method('is')
			->with('post')
			->will($this->returnValue(true));
		$Request->data = array('request_data' => true);

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('saveAll'))
			->getMock();
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(false));
		$Model->id = null;
		$Model->data = array('model_data' => true);

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => new Controller,
			'handleAction' => 'add',
			'action' => 'add',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array()
		));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => false, 'created' => false))
			->will($this->returnValue($CrudSubject));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', 'setFlash', '_redirect'))
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
