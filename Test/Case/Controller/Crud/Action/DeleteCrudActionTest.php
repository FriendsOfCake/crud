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
			'action' => 'view',
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
			->with('action')
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


}
