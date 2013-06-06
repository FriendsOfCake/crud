<?php

App::uses('CakeEvent', 'Event');
App::uses('ComponentCollection', 'Controller');
App::uses('CrudAction', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('CrudComponent', 'Crud.Controller/Component');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudActionText extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		$this->Request = $this->getMock('CakeRequest');
		$this->Collection = $this->getMock('ComponentCollection', null);
		$this->Controller = $this->getMock('Controller');
		$this->Crud = $this->getMock('CrudComponent', null, array($this->Collection));
		$this->handleAction = 'add';

		$this->Subject = new CrudSubject(array(
			'request' => $this->Request,
			'crud' => $this->Crud,
			'collection' => $this->Collection,
			'controller' => $this->Controller,
			'handleAction' => $this->handleAction,
			'action' => $this->handleAction,
			'model' => null,
			'modelClass' => null,
			'args' => array()
		));

		$this->actionClassName = $this->getMockClass('CrudAction', array('_handle'));
		$this->ActionClass = new $this->actionClassName($this->Subject);
		$this->_configureAction($this->ActionClass);
	}

	public function tearDown() {
		parent::tearDown();
		unset(
			$this->Crud,
			$this->Request,
			$this->Collection,
			$this->Controller,
			$this->handleAction,
			$this->Subject,
			$this->ActionClass
		);
	}

	protected function _configureAction($action) {
		$action->config(array(
			'enabled' => true,
			'findMethod' => 'first',
			'view' => null,
			'relatedLists' => true,
			'validateId' => null,
			'saveOptions' => array(
				'validate' => 'first',
				'atomic' => true
			),
			'serialize' => array(
				'success',
				'data'
			)
		));
	}

/**
 * Test that it's possible to override all
 * configuration settings through the __constructor()
 *
 * @return void
 */
	public function testOverrideAllDefaults() {
		$expected = array(
			'enabled' => false,
			'findMethod' => 'any',
			'view' => 'my_view',
			'relatedLists' => array('Tag'),
			'validateId' => 'id',
			'saveOptions' => array(
				'validate' => 'never',
				'atomic' => false
			),
			'serialize' => array(
				'yay',
				'ney'
			),
			'handleAction' => 'add'
		);

		$ActionClass = new $this->actionClassName($this->Subject, $expected);
		// This is injected by the CrudAction, not technically a setting
		$expected['handleAction'] = 'add';
		$actual = $ActionClass->config();
		$this->assertEquals($expected, $actual, 'It was not possible to override all default settings.');
	}

/**
 * Test that we get the expected events
 *
 * @return void
 */
	public function testImplementedEvents() {
		$expected = array();
		$actual = $this->ActionClass->implementedEvents();
		$this->assertEquals($expected, $actual, 'The CrudAction implements events');
	}

/**
 * Test that an enabled action will call _handle
 *
 * @return void
 */
	public function testEnabledActionWorks() {
		$this->ActionClass
			->expects($this->once())
			->method('_handle', '_handle was never called on a enabled action')
			->will($this->returnValue(true));

		$expected = true;
		$actual = $this->ActionClass->config('enabled');
		$this->assertSame($expected, $actual, 'The action is not enabled by default');

		$expected = true;
		$actual = $this->ActionClass->handle($this->Subject);
		$this->assertSame($expected, $actual, 'Calling handle on a disabled action did not return null');
	}

/**
 * Test that an enabled action will call _handle
 *
 * @return void
 */
	public function testHandleReturnsFalseIfHandleActionDoesntMatchRequestAction() {
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->never())
			->method('_handle', '_handle was called');
		$this->ActionClass
			->expects($this->at(0))
			->method('config', 'the action never checked if the action is enabled')
			->with('enabled')
			->will($this->returnValue(true));
		$this->ActionClass
			->expects($this->at(1))
			->method('config', 'the action didn\'t ask for the handleAction property')
			->with('handleAction')
			->will($this->returnValue('admin'));

		// Make sure the action in the request isn't the same as handleAction propety
		$this->Subject->action = 'admin_add';

		$expected = false;
		$actual = $this->ActionClass->handle($this->Subject);
		$this->assertSame($expected, $actual);
	}

/**
 * Test that calling disable() on the action object
 * disables the action and makes the handle method return false
 *
 * @return void
 */
	public function testDisableWorks() {
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->never())
			->method('_handle', '_handle should never be called on a disabled action');
		$this->ActionClass
			->expects($this->once())
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', false);

		$this->ActionClass->disable();
	}

/**
 * Test that calling enable() on the action object
 * enables the action
 *
 * @return void
 */
	public function testEnableWorks() {
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->never())
			->method('_handle', '_handle should never be called on a disabled action');
		$this->ActionClass
			->expects($this->once())
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', true);

		$this->ActionClass->enable();
	}

/**
 * Test that getting the findMethod will execute config()
 *
 * @return void
 */
	public function testFindMethodGet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('findMethod');

		$this->ActionClass->findMethod();
	}

/**
 * Test that setting the findMethod will execute config()
 *
 * @return void
 */
	public function testFindMethodSet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('findMethod', 'my_first');

		$this->ActionClass->findMethod('my_first');
	}

/**
 * Test that getting the saveOptions will execute config()
 *
 * @return void
 */
	public function testSaveOptionsGet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('saveOptions');

		$this->ActionClass->saveOptions();
	}

/**
 * Test that setting the saveOptions will execute config()
 *
 * @return void
 */
	public function testSaveOptionsSet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('saveOptions', array('hello world'));

		$this->ActionClass->saveOptions(array('hello world'));
	}

/**
 * Test that getting the view will execute config()
 *
 * Since there is no view configured, it will call config('handleAction')
 * and use the return value as the view name.
 *
 * @return void
 */
	public function testViewGetWithoutConfiguredView() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->at(0))
			->method('config')
			->with('view');

		$this->ActionClass
			->expects($this->at(1))
			->method('config')
			->with('handleAction')
			->will($this->returnValue('add'));

		$expected = 'add';
		$actual = $this->ActionClass->view();
		$this->assertSame($expected, $actual);
	}

/**
 * Test that getting the view will execute config()
 *
 * Since a view has been configured, the view value will be
 * returned and it won't use handleAction
 *
 * @return void
 */
	public function testViewGetWithConfiguredView() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('view')
			->will($this->returnValue('add'));

		$expected = 'add';
		$actual = $this->ActionClass->view();
		$this->assertSame($expected, $actual);
	}

/**
 * Test that setting the saveOptions will execute config()
 *
 * @return void
 */
	public function testViewSet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('view', 'my_view');

		$this->ActionClass->view('my_view');
	}

/**
 * Test that getting the ID from request without any id in the request
 * object will return null
 *
 * @return void
 */
	public function testGetIdFromRequestWithoutPassZeroIndex() {
		$expected = null;
		$actual = $this->ActionClass->getIdFromRequest();
		$this->assertSame($expected, $actual);
	}

/**
 * Test that getting the ID from request with an id in the request
 * object will return the correct ID
 *
 * @return void
 */
	public function testGetIdFromRequestWithPassZeroIndex() {
		$this->Request->params['pass'][0] = 1;

		$expected = 1;
		$actual = $this->ActionClass->getIdFromRequest();
		$this->assertSame($expected, $actual);
	}

}
