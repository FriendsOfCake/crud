<?php

App::uses('CakeEvent', 'Event');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('SessionComponent', 'Controller/Component');
App::uses('CrudAction', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('IndexCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudTestCase', 'Crud.Test/Support');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudActionTest extends CrudTestCase {

	public function setUp() {
		parent::setUp();

		$this->Request = $this->getMock('CakeRequest');
		$this->Collection = $this->getMock('ComponentCollection', null);
		$this->Controller = $this->getMock('Controller');
		$this->Controller->Components = $this->Collection;
		$this->Crud = $this->getMock('CrudComponent', null, array($this->Collection));
		$this->Model = $this->getMock('Model');
		$this->Model->name = '';
		$this->action = 'add';

		$this->Subject = new CrudSubject(array(
			'request' => $this->Request,
			'crud' => $this->Crud,
			'controller' => $this->Controller,
			'action' => $this->action,
			'model' => $this->Model,
			'modelClass' => '',
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
			$this->action,
			$this->Subject,
			$this->ActionClass
		);
	}

	protected function _configureAction($action) {
		$action->config(array(
			'enabled' => true,
			'findMethod' => 'first',
			'view' => null,
			'relatedModels' => true,
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
			'relatedModels' => array('Tag'),
			'validateId' => 'id',
			'saveOptions' => array(
				'validate' => 'never',
				'atomic' => false
			),
			'serialize' => array(
				'yay',
				'ney'
			),
			'action' => 'add'
		);

		$ActionClass = new $this->actionClassName($this->Subject, $expected);
		// This is injected by the CrudAction, not technically a setting
		$expected['action'] = 'add';
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
		$Request = new CakeRequest();
		$Request->action = 'add';

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_request', '_handle', 'enforceRequestType'))
			->getMock();
		$Action
			->expects($this->any())
			->method('_request')
			->with()
			->will($this->returnValue($Request));
		$Action
			->expects($this->once())
			->method('_handle', '_handle was never called on a enabled action')
			->will($this->returnValue(true));

		$this->_configureAction($Action);
		$Action->config('action', 'add');

		$expected = true;
		$actual = $Action->config('enabled');
		$this->assertSame($expected, $actual, 'The action is not enabled by default');

		$expected = true;
		$actual = $Action->handle($this->Subject);
		$this->assertSame($expected, $actual, 'Calling handle on a disabled action did not return null');
	}

/**
 * Test that calling disable() on the action object
 * disables the action and makes the handle method return false
 *
 * @return void
 */
	public function testDisableWorks() {
		$this->Controller->methods[] = 'add';

		$this->Subject->set(array('action' => 'add'));
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->never())
			->method('_handle', '_handle should never be called on a disabled action');
		$this->ActionClass
			->expects($this->once())
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', false);

		$this->ActionClass->disable();

		$actual = array_search('add', $this->Controller->methods);
		$this->assertFalse($actual, '"add" was not removed from the controller::$methods array');
	}

/**
 * Test that calling enable() on the action object
 * enables the action
 *
 * @return void
 */
	public function testEnableWorks() {
		$this->Subject->set(array('action' => 'add'));
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->never())
			->method('_handle', '_handle should never be called on a disabled action');
		$this->ActionClass
			->expects($this->once())
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', true);

		$this->ActionClass->enable();

		$actual = array_search('add', $this->Controller->methods);
		$this->assertNotEmpty($actual, '"add" was not added to the controller::$methods array');
	}

/**
 * Test that getting the findMethod will execute config()
 *
 * @return void
 */
	public function testFindMethodGet() {
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
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
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
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
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
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
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('saveOptions', array('hello world'));

		$this->ActionClass->saveOptions(array('hello world'));
	}

/**
 * Test that getting the view will execute config()
 *
 * Since there is no view configured, it will call config('action')
 * and use the return value as the view name.
 *
 * @return void
 */
	public function testViewGetWithoutConfiguredView() {
		$this->Request->action = 'add';
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->at(0))
			->method('config')
			->with('view');

		$expected = 'add';
		$actual = $this->ActionClass->view();
		$this->assertSame($expected, $actual);
	}

/**
 * Test that getting the view will execute config()
 *
 * Since a view has been configured, the view value will be
 * returned and it won't use action
 *
 * @return void
 */
	public function testViewGetWithConfiguredView() {
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
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
		$this->ActionClass = $this->getMock('CrudAction', array('config', '_handle'), array($this->Subject));
		$this->ActionClass
			->expects($this->once())
			->method('config')
			->with('view', 'my_view');

		$this->ActionClass->view('my_view');
	}

/**
 * Test that setFlash triggers the correct methods
 *
 * @return void
 */
	public function testSetFlash() {
		$data = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message success',
				'original' => 'Hello'
			),
			'key' => 'flash',
			'type' => 'add.success',
			'name' => 'test',
			'text' => 'Hello',
		);
		$object = (object)$data;

		$this->Subject->crud = $this->getMock('CrudComponent', array('trigger'), array($this->Collection));
		$this->Subject->crud
			->expects($this->once())
			->method('trigger')
			->with('setFlash', $data)
			->will($this->returnValue($object));

		$this->Subject->crud->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Subject->crud->Session
			->expects($this->once())
			->method('setFlash')
			->with($object->text, $object->element, $object->params, $object->key);

		$this->ActionClass = new $this->actionClassName($this->Subject);
		$this->ActionClass->config('name', 'test');
		$this->ActionClass->config('messages', array('success' => array('text' => 'hello')));
		$this->ActionClass->setFlash('success');
	}

/**
 * Test that detecting the correct validation strategy for validateId
 * works as expected
 *
 * @return void
 */
	public function testDetectPrimaryKeyFieldType() {
		$Model = $this->getMock('Model', array('schema'));
		$Model
			->expects($this->at(0))
			->method('schema')
			->with('id')
			->will($this->returnValue(false));

		$Model
			->expects($this->at(1))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 36, 'type' => 'string')));

		$Model
			->expects($this->at(2))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 10, 'type' => 'integer')));

		$Model
			->expects($this->at(3))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 10, 'type' => 'string')));

		$this->assertFalse($this->ActionClass->detectPrimaryKeyFieldType($Model));
		$this->assertSame('uuid', $this->ActionClass->detectPrimaryKeyFieldType($Model));
		$this->assertSame('integer', $this->ActionClass->detectPrimaryKeyFieldType($Model));
		$this->assertFalse($this->ActionClass->detectPrimaryKeyFieldType($Model));
	}

/**
 * Test default saveAll options works when modified
 *
 * @return void
 */
	public function testGetSaveAllOptionsDefaults() {
		$CrudAction = $this->ActionClass;

		$expected = array(
			'validate' => 'first',
			'atomic' => true
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEqual($expected, $actual);

		$CrudAction->config('saveOptions.atomic', true);
		$expected = array(
			'validate' => 'first',
			'atomic' => true
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEqual($expected, $actual);

		$CrudAction->config('saveOptions', array(
			'fieldList' => array('hello')
		));
		$expected = array(
			'validate' => 'first',
			'atomic' => true,
			'fieldList' => array('hello')
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEqual($expected, $actual);
	}

/**
 * Test that defining specific action configuration for saveAll takes
 * precedence over default configurations
 *
 * @return void
 */
	public function testGetSaveAllOptionsCustomAction() {
		$expected = array('validate' => 'first', 'atomic' => true);
		$actual = $this->ActionClass->saveOptions();
		$this->assertEqual($expected, $actual);

		$this->ActionClass->saveOptions(array('atomic' => false));
		$expected = array('validate' => 'first', 'atomic' => false);
		$actual = $this->ActionClass->saveOptions();
		$this->assertEqual($expected, $actual);
	}

/**
 * testEmptyMessage
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Missing message type
 */
	public function testEmptyMessage() {
		$this->ActionClass->message(null);
	}

/**
 * testUndefinedMessage
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Invalid message type "not defined"
 */
	public function testUndefinedMessage() {
		$this->ActionClass->message('not defined');
	}

/**
 * testBadMessageConfig
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Invalid message config for "badConfig" no text key found
 */
	public function testBadMessageConfig() {
		$this->Crud->config('messages.badConfig', array('foo' => 'bar'));
		$this->ActionClass->message('badConfig');
	}

/**
 * testInheritedSimpleMessage
 *
 * @return void
 */
	public function testInheritedSimpleMessage() {
		$this->Crud->config('messages.simple', 'Simple message');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Simple message'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => '',
			'text' => 'Simple message'
		);
		$actual = $this->ActionClass->message('simple');
		$this->assertEqual($expected, $actual);
	}

/**
 * testOverridenSimpleMessage
 *
 * @return void
 */
	public function testOverridenSimpleMessage() {
		$this->Crud->config('messages.simple', 'Simple message');
		$this->ActionClass->config('messages.simple', 'Overridden message');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Overridden message'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => '',
			'text' => 'Overridden message'
		);
		$actual = $this->ActionClass->message('simple');
		$this->assertEqual($expected, $actual);
	}

/**
 * testSimpleMessage
 *
 * @return void
 */
	public function testSimpleMessage() {
		$this->ActionClass->config('messages.simple', 'Simple message');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Simple message'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => '',
			'text' => 'Simple message'
		);
		$actual = $this->ActionClass->message('simple');
		$this->assertEqual($expected, $actual);
	}

/**
 * testSimpleMessageWithPlaceholders
 *
 * @return void
 */
	public function testSimpleMessageWithPlaceholders() {
		$this->Crud->config('messages.simple', 'Simple message with id "{id}"');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Simple message with id "{id}"'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => '',
			'text' => 'Simple message with id "123"'
		);
		$actual = $this->ActionClass->message('simple', array('id' => 123));
		$this->assertEqual($expected, $actual);
	}

/**
 * testInvalidIdMessage
 *
 * @return void
 */
	public function testInvalidIdMessage() {
		$expected = array(
			'code' => 400,
			'class' => 'BadRequestException',
			'element' => 'default',
			'params' => array(
				'class' => 'message invalidId',
				'original' => 'Invalid id'
			),
			'key' => 'flash',
			'type' => 'add.invalidId',
			'name' => '',
			'text' => 'Invalid id'
		);
		$actual = $this->ActionClass->message('invalidId');
		$this->assertEqual($expected, $actual);
	}

	public function testRecordNotFoundMessage() {
		$expected = array(
			'code' => 404,
			'class' => 'NotFoundException',
			'element' => 'default',
			'params' => array(
				'class' => 'message recordNotFound',
				'original' => 'Not found'
			),
			'key' => 'flash',
			'type' => 'add.recordNotFound',
			'name' => '',
			'text' => 'Not found'
		);
		$actual = $this->ActionClass->message('recordNotFound');
		$this->assertEqual($expected, $actual);
	}

/**
 * testBadRequestMethodMessage
 *
 * @return void
 */
	public function testBadRequestMethodMessage() {
		$expected = array(
			'code' => 405,
			'class' => 'MethodNotAllowedException',
			'element' => 'default',
			'params' => array(
				'class' => 'message badRequestMethod',
				'original' => 'Method not allowed. This action permits only {methods}'
			),
			'key' => 'flash',
			'type' => 'add.badRequestMethod',
			'name' => '',
			'text' => 'Method not allowed. This action permits only THESE ONES'
		);
		$actual = $this->ActionClass->message('badRequestMethod', array('methods' => 'THESE ONES'));
		$this->assertEqual($expected, $actual);
	}

/**
 * Test that it's possible to change just one sub key
 * by providing all the parents, without loosing any
 * default settings
 *
 * @return void
 */
	public function testConfigMergeWorks() {
		$this->ActionClass->config('messages.invalidId', array(
			'code' => 400,
			'class' => 'BadRequestException',
			'text' => 'Invalid id'
		));
		$this->ActionClass->config(array('messages' => array('invalidId' => array('code' => 500))));

		$expected = array(
			'code' => 500,
			'class' => 'BadRequestException',
			'text' => 'Invalid id'
		);
		$result = $this->ActionClass->config('messages.invalidId');
		$this->assertEqual($result, $expected);
	}

/**
 * testRequestMethods
 *
 * @covers CrudAction::requestMethods
 * @return void
 */
	public function testRequestMethods() {
		$this->ActionClass->requestMethods('default', array('get', 'post', 'put', 'delete'));
		$result = $this->ActionClass->requestMethods('default');
		$expected = array('get', 'post', 'put', 'delete');
		$this->assertEqual($result, $expected);
	}

/**
 * testRequestMethodsDefaults
 *
 * @covers CrudAction::requestMethods
 * @return void
 */
	public function testRequestMethodsDefaults() {
		$defaults = array('requestMethods' => array('default' => array('get', 'put')));
		$this->ActionClass = new $this->actionClassName($this->Subject, $defaults);

		$result = $this->ActionClass->requestMethods('default');
		$expected = array('get', 'put');
		$this->assertEqual($result, $expected);
	}

/**
 * testRequestMethodDefaultOverride
 *
 * Test that providing defaults override crud action defaults
 *
 * @covers CrudAction::requestMethods
 * @return void
 */
	public function testRequestMethodDefaultOverride() {
		$defaults = array(
			'requestMethods' => array(
				'default' => array('put')
			)
		);

		$this->ActionClass = $this->getMock('IndexCrudAction', array('foo'), array($this->Subject, $defaults));

		$result = $this->ActionClass->requestMethods('default');
		$expected = array('put');
		$this->assertEqual($result, $expected);

		$result = $this->ActionClass->requestMethods('default', array('get'));
		$expected = $this->ActionClass;
		$this->assertEqual($result, $expected);

		$result = $this->ActionClass->requestMethods('default');
		$expected = array('get');
		$this->assertEqual($result, $expected);
	}

/**
 * testEnforceRequestType
 *
 * Test that requesting an action with an valid action does not throw an exception
 *
 * @covers CrudAction::enforceRequestType
 * @return void
 */
	public function testEnforceRequestType() {
		$request = $this->getMock('CakeRequest', array('is'));
		$request->expects($this->at(0))->method('is')->with('get')->will($this->returnValue(false));
		$request->expects($this->at(1))->method('is')->with('post')->will($this->returnValue(true));

		$action = $this->getMock('IndexCrudAction', array('_request', 'requestMethods'), array(new CrudSubject()));
		$action->expects($this->once())->method('requestMethods')->will($this->returnValue(array('get', 'post')));
		$action->expects($this->once())->method('_request')->with()->will($this->returnValue($request));
		$action->enforceRequestType();
	}

/**
 * testEnforceRequestTypeInvalidRequestType
 *
 * Test that requesting an action with an invalid action throws an exception
 *
 * @covers CrudAction::enforceRequestType
 * @expectedException MethodNotAllowedException
 * @return void
 */
	public function testEnforceRequestTypeInvalidRequestType() {
		$request = $this->getMock('CakeRequest', array('is'));
		$request->expects($this->at(0))->method('is')->with('get')->will($this->returnValue(false));
		$request->expects($this->at(1))->method('is')->with('post')->will($this->returnValue(false));

		$action = $this->getMock('IndexCrudAction', array('_request', 'requestMethods'), array(new CrudSubject()));
		$action->expects($this->once())->method('_request')->with()->will($this->returnValue($request));
		$action->expects($this->once())->method('requestMethods')->will($this->returnValue(array('get', 'post')));
		$action->enforceRequestType();
	}

/**
 * testHandle
 *
 * Test that calling handle will invoke _handle
 * when the action is enabbled
 *
 * @covers CrudAction::handle
 * @return void
 */
	public function testHandle() {
		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', 'enforceRequestType', '_handle'))
			->getMock();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('enforceRequestType');
		$Action
			->expects($this->at($i++))
			->method('_handle');

		$Action->handle(new CrudSubject(array('args' => array())));
	}

/**
 * testHandleDisabled
 *
 * Test that calling handle will not invoke _handle
 * when the action is disabled
 *
 * @covers CrudAction::handle
 * @return void
 */
	public function testHandleDisabled() {
		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', 'enforceRequestType', '_handle'))
			->getMock();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('enforceRequestType');
		$Action
			->expects($this->never())
			->method('_handle');

		$Action->handle(new CrudSubject(array('args' => array())));
	}

}
