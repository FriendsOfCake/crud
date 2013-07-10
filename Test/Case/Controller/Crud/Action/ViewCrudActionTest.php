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

// @codingStandardsIgnoreStart
	protected $ModelMock;

	protected $ControllerMock;

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;
// @codingStandardsIgnoreEnd

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
			->setMethods(array('create', 'escapeField', 'find', 'saveAll'))
			->getMock();
		$Model->name = 'Example';

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
			'action' => 'view',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', '_validateId', 'setFlash', '_redirect'))
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
			->with('action')
			->will($this->returnValue('view'));
		$Action
			->expects($this->once())
			->method('_handle');

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
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

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

		$Controller
			->expects($this->once())
			->method('set')
			->with(array('example' => $data, 'success' => true));

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
 * This test checks if changing the viewVar name
 * also changes the key sent to Controller::set
 *
 * @return void
 */
	public function testActionGetChangwViewVarName() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

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

		$Action->viewVar('data');
		$Controller
			->expects($this->once())
			->method('set')
			->with(array('data' => $data, 'success' => true));

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
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array();

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
			->will($this->returnValue(new CrudSubject(array('item' => $data, 'id' => 1))));
		$Crud
			->expects($this->exactly(2))
			->method('trigger');

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

		$Controller
			->expects($this->never())
			->method('set');

		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));

		$this->setExpectedException('NotFoundException');

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
		extract($this->_mockClasses());

		$Crud
			->expects($this->exactly(0))
			->method('trigger');

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
		extract($this->_mockClasses());

		$Request->params['pass'][0] = null;
		$CrudSubject->args = array();

		$Action
			->expects($this->once())
			->method('_validateId')
			->with(null)
			->will($this->returnValue(false));

		$expected = false;
		$actual = $Action->handle($CrudSubject);

		$this->assertSame($expected, $actual);
	}

}
