<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('EditCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudComponent', 'Crud.Controlller/Component');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class EditCrudActionTest extends CakeTestCase {

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
		$this->ActionMock = $this->getMockBuilder('EditCrudAction');
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
			'action' => 'edit',
			'action' => 'edit',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array(
				'enabled',
				'config',
				'setFlash',
				'getIdFromRequest',
				'_getFindMethod',
				'_redirect',
				'_validateId'
			))
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
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_handle');

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionGet() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterFind', array('id' => 1));
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

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->once())
			->method('_getFindMethod')
			->with('first')
			->will($this->returnValue('first'));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the best possible case
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionPut() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Request->data = $data;
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(true));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave', array('id' => 1));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => true, 'created' => false, 'id' => 1))
			->will($this->returnValue($CrudSubject));
		$Crud
			->expects($this->exactly(2))
			->method('trigger');

		$Model
			->expects($this->once())
			->method('saveAll')
			->with($data)
			->will($this->returnValue(true));

		$CrudSubject->set(array(
			'url' => '/'
		));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->at(2))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at(3))
			->method('saveOptions')
			->will($this->returnValue(array('atomic' => true)));
		$Action
			->expects($this->at(4))
			->method('setFlash')
			->with('success');
		$Action
			->expects($this->at(5))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the saveAll() call fails
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionPutSaveError() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Request->data = $data;
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(true));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave', array(
				'id' => 1
			));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => false, 'created' => false, 'id' => 1))
			->will($this->returnValue($CrudSubject));
		$Crud
			->expects($this->at(2))
			->method('trigger')
			->with('beforeRender');
		$Crud
			->expects($this->exactly(3))
			->method('trigger');

		$Model
			->expects($this->once())
			->method('saveAll')
			->with($data)
			->will($this->returnValue(false));

		$CrudSubject->set(array(
			'url' => '/'
		));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->at(2))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at(3))
			->method('saveOptions')
			->will($this->returnValue(array('atomic' => true)));
		$Action
			->expects($this->at(4))
			->method('setFlash')
			->with('error');

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * This test assumes there is no id provided in the URL
 * and thus it should halt after the _validateId method
 *
 * @return void
 */
	public function testActionGetWithoutId() {
		extract($this->_mockClasses());

		$CrudSubject->args = array();

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(null)
			->will($this->returnValue(false));
		$Action
			->expects($this->once())
			->method('getIdFromRequest')
			->will($this->returnValue(null));

		$expected = false;
		$actual = $Action->handle($CrudSubject);

		$this->assertSame($expected, $actual);
	}

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * This test assumes there is no id provided in the URL
 * and thus it should halt after the _validateId method
 *
 * Validate that if no id is passed through args, then it
 * will be extracted from the request.
 *
 * We return false in '_validateId()' to avoid mocking the rest of the
 * test since it's not really interesting for this case anyway
 *
 * @return void
 */
	public function testActionGetWithIdFromRequest() {
		extract($this->_mockClasses());

		$CrudSubject->args = array();

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(false));
		$Action
			->expects($this->once())
			->method('getIdFromRequest')
			->will($this->returnValue(1));

		$expected = false;
		$actual = $Action->handle($CrudSubject);

		$this->assertSame($expected, $actual);
	}

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * Given an ID, we test what happens if the ID doesn't
 * exist in the database
 *
 * @return void
 */
	public function testActionGetWithNonexistingId() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));

		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array('findMethod' => 'first', 'query' => $query))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('recordNotFound', array('id' => 1))
			->will($this->returnValue($CrudSubject));
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
			->will($this->returnValue(array()));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->once())
			->method('_getFindMethod')
			->with('first')
			->will($this->returnValue('first'));

		$this->setExpectedException('NotFoundException');

		$Action->handle($CrudSubject);
	}

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 *
 * The id provided, it's correct and it's in the db
 * Additionally the `_getFindMethod` method returns
 * something not-default
 *
 * @return void
 */
	public function testGetWithCustomFindMethod() {
		extract($this->_mockClasses());

		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array('findMethod' => 'customFindMethod', 'query' => $query))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'customFindMethod'))));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterFind', array('id' => 1));
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
			->with('customFindMethod', $query)
			->will($this->returnValue($data));

		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->once())
			->method('_getFindMethod')
			->with('first')
			->will($this->returnValue('customFindMethod'));

		$Action->handle($CrudSubject);
	}

}
