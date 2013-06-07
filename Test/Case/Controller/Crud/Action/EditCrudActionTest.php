<?php

App::uses('Model', 'Model');
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

	protected $ModelMock;

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ActionMock = $this->getMockBuilder('EditCrudAction');
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
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_handle');

		$CrudSubject = new CrudSubject(array(
			'action' => 'edit',
			'model' => new StdClass(),
			'modelClass' => 'Blog',
			'args' => array()
		));

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
		$query = array(
			'conditions' => array(
				'Model.id' => 1
			)
		);

		$data = array('Model' => array('id' => 1));

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
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

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
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
			->with('handleAction')
			->will($this->returnValue('edit'));
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));

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
		$CrudSubject = new CrudSubject();

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

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(true));
		$Request->data = $data;

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeSave', array(
				'id' => 1
			));
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterSave', array('success' => true, 'created' => false, 'id' => 1))
			->will($this->returnValue($CrudSubject));
		$Crud
			->expects($this->exactly(2))
			->method('trigger');

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('saveAll'))
			->getMock();
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($data)
			->will($this->returnValue(true));

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1),
			'url' => '/'
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', '_redirect', 'saveOptions'))
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
			->with('update.success');
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
		$CrudSubject = new CrudSubject();

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

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(true));
		$Request->data = $data;

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
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

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('saveAll'))
			->getMock();
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($data)
			->will($this->returnValue(false));

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1),
			'url' => '/'
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', 'saveOptions'))
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
			->with('update.error');

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
		$CrudSubject = new CrudSubject(array(
			'crud' => null,
			'request' => null,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
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
			->with('handleAction')
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
		$CrudSubject = new CrudSubject(array(
			'crud' => null,
			'request' => null,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
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
			->with('handleAction')
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
		$query = array(
			'conditions' => array(
				'Model.id' => 1
			)
		);

		$CrudSubject = new CrudSubject();

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
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
			->with('recordNotFound', array('id' => 1))
			->will($this->returnValue($CrudSubject));
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
			->will($this->returnValue(array()));

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
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
			->with('handleAction')
			->will($this->returnValue('edit'));
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
			->with($CrudSubject, array('action' => 'index'));

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
		$query = array(
			'conditions' => array(
				'Model.id' => 1
			)
		);

		$data = array('Model' => array('id' => 1));

		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('put')
			->will($this->returnValue(false));

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforeFind', array(
				'findMethod' => 'customFindMethod',
				'query' => $query
			))
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
			->with('customFindMethod', $query)
			->will($this->returnValue($data));

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'collection' => null,
			'controller' => null,
			'handleAction' => 'edit',
			'action' => 'edit',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', '_getFindMethod'))
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
