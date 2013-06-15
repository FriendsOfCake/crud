<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('DeleteCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('ComponentCollection', 'Controller');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class DeleteCrudActionText extends CakeTestCase {

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
		$this->ActionMock = $this->getMockBuilder('DeleteCrudAction');
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
		$Request = $this->RequestMock
			->setMethods(null)
			->getMock();
		$Request->params['pass'][0] = 1;

		$CrudSubject = new CrudSubject();

		$Component = $this->getMock('ComponentCollection', null);

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();
		$Controller->modelClass = 'Model';
		$Controller->Components = $Component;
		$Controller->request = $Request;
		$Controller->response = new CakeResponse();

		$Crud = $this->CrudMock
			->setConstructorArgs(array($Component))
			->setMethods(null)
			->getMock();

		$Crud->initialize($Controller);

		// $Crud->Session = $this->getMock('stdClass', array('setFlash'));

		$Model = $this->ModelMock
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'schema', 'delete'))
			->getMock();

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => $Controller,
			'action' => 'delete',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array()
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('setFlash', '_redirect'))
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
			->setMethods(array('_handle'))
			->getMock();

		$Action
			->expects($this->once())
			->method('_handle');

		$Action->handle($CrudSubject);
	}

/**
 * Test that the Crud.beforeDelete method can stop a delete
 *
 * @return void
 */
	public function testDeleteCanBeStoppedFromEvent() {
		extract($this->_mockClasses());

		$Request->addDetector('delete', array('callback' => function() {
			return true;
		}));

		$Crud->on('Crud.beforeDelete', function(CakeEvent $event) {
			$event->stopPropagation();
			return false;
		});

		$Model
			->expects($this->once())
			->method('find')
			->with('count')
			->will($this->returnValue(1));

		$Model
			->expects($this->never())
			->method('delete');

		$Action
			->expects($this->once())
			->method('setFlash')
			->with('delete.error');

		$Action
			->expects($this->once())
			->method('_redirect');

		$Action->handle($CrudSubject);
	}

/**
 * Test that the Crud.beforeDelete method can stop a delete
 *
 * @return void
 */
	public function testAfterDeleteIsCalledOnFailure() {
		extract($this->_mockClasses());

		$Request->addDetector('delete', array('callback' => function() {
			return true;
		}));

		$Model
			->expects($this->once())
			->method('find')
			->with('count')
			->will($this->returnValue(1));

		$Model
			->expects($this->once())
			->method('delete')
			->with(1)
			->will($this->returnValue(false));

		$Action
			->expects($this->once())
			->method('setFlash')
			->with('delete.error');

		$Action
			->expects($this->once())
			->method('_redirect');

		$Action->handle($CrudSubject);
	}
}
