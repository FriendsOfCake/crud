<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('ViewCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudComponent', 'Crud.Controlller/Component');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ViewCrudActionTest extends CakeTestCase {

	protected $ModelMock;

	protected $ControllerMock;

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ControllerMock = $this->getMockBuilder('Controller');
		$this->ActionMock = $this->getMockBuilder('ViewCrudAction');
		$this->RequestMock = $this->getMockBuilder('CakeRequest');
		$this->CrudMock = $this->getMockBuilder('CrudComponent');
	}

	public function tearDown() {
		parent::tearDown();

		unset(
			$this->ModelMock,
			$this->Controller,
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
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_handle');

		$CrudSubject = new CrudSubject(array(
			'action' => 'view',
			'model' => new StdClass(),
			'modelClass' => 'Blog',
			'args' => array()
		));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 *
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionGet() {
		$query = array(
			'conditions' => array(
				'Model.id' => 1
			)
		);

		$data = array(
			'Model' => array(
				'id' => 1
			)
		);

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query,
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterFind', array(
				'id' => 1,
				'item' => $data
			))
			->will($this->returnValue(new CrudSubject(array('item' => $data))));
		$Crud
			->expects($this->at(2))
			->method('trigger')
			->with('beforeRender');
		$Crud
			->expects($this->exactly(3))
			->method('trigger');

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Model
			->expects($this->once())
			->method('find')
			->with('first', $query)
			->will($this->returnValue($data));

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$Controller
			->expects($this->once())
			->method('set')
			->with(array('item' => $data, 'success' => true));

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => null,
			'controller' => $Controller,
			'action' => 'view',
			'action' => 'view',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes that the id for the view
 * action does not exist in the database
 *
 * @return void
 */
	public function testActionGetIdDontExist() {
		$query = array(
			'conditions' => array(
				'Model.id' => 1
			)
		);

		$data = array();

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query,
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('recordNotFound', array(
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('item' => $data))));
		$Crud
			->expects($this->exactly(2))
			->method('trigger');

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Model
			->expects($this->once())
			->method('find')
			->with('first', $query)
			->will($this->returnValue($data));

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$Controller
			->expects($this->never())
			->method('set');

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => null,
			'controller' => $Controller,
			'action' => 'view',
			'action' => 'view',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', '_redirect'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->once())
			->method('setFlash')
			->with('find.error');
		$Action
			->expects($this->once())
			->method('_redirect')
			->with(new CrudSubject(array('item' => $data)), array('action' => 'index'));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes the id is invalid
 *
 * @return void
 */
	public function testActionGetInvalidId() {
		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->exactly(0))
			->method('trigger');

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => null,
			'controller' => $Controller,
			'action' => 'view',
			'action' => 'view',
			'model' => null,
			'modelClass' => null,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(false));

		$expected = false;
		$actual = $Action->handle($CrudSubject);

		$this->assertSame($expected, $actual);
	}

/**
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test fakes that the id is invalid
 * since we don't care for the rest of the code
 *
 * @return void
 */
	public function testActionGetMissingId() {
		$Request = $this->RequestMock
			->disableOriginalConstructor()
			->getMock();

		$CrudSubject = new CrudSubject(array(
			'crud' => null,
			'request' => $Request,
			'controller' => (object)array('Components' => null),
			'action' => 'view',
			'action' => 'view',
			'model' => null,
			'modelClass' => null,
			'args' => array()
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'getIdFromRequest'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(null)
			->will($this->returnValue(false));
		$Action
			->expects($this->once())
			->method('getIdFromRequest');

		$expected = false;
		$actual = $Action->handle($CrudSubject);

		$this->assertSame($expected, $actual);
	}

}
