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
class DeleteCrudActionText extends CakeTestCase {

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

	protected function _mockClasses() {
		$CrudSubject = new CrudSubject();

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();
		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();
		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();
		$Request = $this->RequestMock
			->setMethods(array('is'))
			->getMock();
		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config', '_validateId', 'setFlash', '_redirect'))
			->getMock();

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => $Controller,
			'handleAction' => 'view',
			'action' => 'view',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(1)
		));

		return compact('Crud', 'Model', 'Controller', 'Request', 'CrudSubject', 'Action');
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
			->will($this->returnValue('delete'));
		$Action
			->expects($this->once())
			->method('_handle');

		$CrudSubject = new CrudSubject(array(
			'action' => 'delete',
			'model' => new StdClass(),
			'modelClass' => 'Blog',
			'args' => array()
		));

		$Action->handle($CrudSubject);
	}

/**
 * testDeleteActionExists
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteActionExists() {
		// $this->controller
		// 	->expects($this->never())
		// 	->method('render');

		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return true; }
		// ));

		// $this->Crud->settings['validateId'] = 'notUuid';
		// $id = 1;

		// $this->Crud->executeAction('delete', array($id));

		// $count = $this->model->find('count', array('conditions' => array('id' => $id)));
		// $this->assertSame(0, $count);

		// $events = CakeEventManager::instance()->getLog();
		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteActionDoesNotExists
 *
 * If you try to delete something that doesn't exist - it should issue a recordNotFound event
 * TODO it should /not/ issue a beforeDelete event but it currently does
 */
	public function testDeleteActionDoesNotExists() {
		// $this->controller
		// 	->expects($this->never())
		// 	->method('render');

		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return true; }
		// ));

		// $this->Crud->settings['validateId'] = 'notUuid';
		// $id = 42;
		// $this->Crud->executeAction('delete', array($id));

		// $count = $this->model->find('count');
		// $this->assertSame(3, $count);

		// $events = CakeEventManager::instance()->getLog();

		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertSame(false, $index, "Crud.beforeDelete event triggered");

		// $index = array_search('Crud.recordNotFound', $events);
		// $this->assertNotSame(false, $index, "A none-existent row did not trigger a Crud.recordNotFount event");
	}

/**
 * testDeleteWorksWhenDELETEandSecureDelete
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteWorksWhenDELETEandSecureDelete() {
		// $this->controller
		// 	->expects($this->never())
		// 	->method('render');

		// $CrudAction = $this->Crud->getAction('delete');
		// $CrudAction->config('secureDelete', true);

		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return true; }
		// ));

		// $CrudAction->config('validateId', 'integer');
		// $id = 1;

		// $this->Crud->executeAction('delete', array($id));

		// $count = $this->model->find('count', array('conditions' => array('id' => $id)));
		// $this->assertSame(0, $count);

		// $events = CakeEventManager::instance()->getLog();
		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteFailsWithPostAndSecureDeleteActive
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteFailsWithPostAndSecureDeleteActive() {
		// $this->controller
		// 	->expects($this->never())
		// 	->method('render');

		// $CrudAction = $this->Crud->getAction('delete');
		// $CrudAction->config('secureDelete', true);

		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return false; }
		// ));

		// $this->controller->request->addDetector('post', array(
		// 	'callback' => function() { return true; }
		// ));

		// $CrudAction->config('validateId', 'integer');
		// $id = 1;

		// $this->Crud->executeAction('delete', array($id));

		// $count = $this->model->find('count', array('conditions' => array('id' => $id)));
		// $this->assertSame(1, $count);

		// $events = CakeEventManager::instance()->getLog();
		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertSame(false, $index, "There was a Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertSame(false, $index, "There was a Crud.beforeDelete event triggered");

		// $index = array_search('Crud.setFlash', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteWorksWithPostAndSecureDeleteDisabled
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteWorksWithPostAndSecureDeleteActive() {
		// $this->controller
		// 	->expects($this->never())
		// 	->method('render');

		// $CrudAction = $this->Crud->getAction('delete');
		// $CrudAction->config('secureDelete', false);

		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return false; }
		// ));

		// $this->controller->request->addDetector('post', array(
		// 	'callback' => function() { return true; }
		// ));

		// $CrudAction->config('validateId', 'integer');
		// $id = 1;

		// $this->Crud->executeAction('delete', array($id));

		// $count = $this->model->find('count', array('conditions' => array('id' => $id)));
		// $this->assertSame(0, $count);

		// $events = CakeEventManager::instance()->getLog();
		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertNotSame(false, $index, "There was a Crud.beforeDelete event triggered");
	}

/**
 * Test if custom finders work in delete
 *
 * @return void
 */
	public function testCustomFindDeletePublished() {
		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return true; }
		// ));

		// $CrudAction = $this->Crud->getAction('delete');
		// $CrudAction->config('validateId', 'integer');
		// $CrudAction->findMethod('firstPublished');

		// $this->Crud->executeAction('delete', array(2));

		// $events = CakeEventManager::instance()->getLog();

		// $index = array_search('Crud.recordNotFound', $events);
		// $this->assertSame(false, $index, "Crud.recordNotFound event triggered");

		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");

		// $count = $this->model->find('count');
		// $this->assertEquals(2, $count);
	}

/**
 * Test if custom finders work in delete - part 2
 *
 * @return void
 */
	public function testCustomFindDeleteUnpublished() {
		// $this->controller->request->addDetector('delete', array(
		// 	'callback' => function() { return true; }
		// ));

		// $this->Crud->settings['validateId'] = 'integer';
		// $this->Crud->mapFindMethod('delete', 'firstUnpublished');
		// $this->Crud->executeAction('delete', array(2));

		// $events = CakeEventManager::instance()->getLog();

		// $index = array_search('Crud.recordNotFound', $events);
		// $this->assertNotSame(false, $index, "Crud.recordNotFound event triggered");

		// $index = array_search('Crud.beforeDelete', $events);
		// $this->assertSame(false, $index, "Crud.beforeDelete event triggered");

		// $index = array_search('Crud.afterDelete', $events);
		// $this->assertSame(false, $index, "Crud.afterDelete event triggered");

		// $count = $this->model->find('count');
		// $this->assertEquals(3, $count);
	}

}
