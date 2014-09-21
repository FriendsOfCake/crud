<?php
namespace Crud\TestCase\Action;

use Crud\Event\Subject;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class CrudActionTest extends TestCase {

	public function setUp() {
		parent::setUp();

		$this->Request = $this->getMock('Cake\Network\Request');
		$this->Controller = $this->getMock(
			'Cake\Controller\Controller',
			null,
			array($this->Request, new \Cake\Network\Response, 'CrudExamples', \Cake\Event\EventManager::instance())
		);
		$this->Registry = $this->Controller->components();
		$this->Crud = $this->getMock('Crud\Controller\Component\CrudComponent', null, array($this->Registry));
		$this->Model = $this->getMock('Cake\ORM\Table');
		$this->Model->name = '';
		$this->action = 'add';

		$this->Subject = new Subject(array(
			'request' => $this->Request,
			'crud' => $this->Crud,
			'controller' => $this->Controller,
			'action' => $this->action,
			'model' => $this->Model,
			'modelClass' => '',
			'args' => array()
		));

		$this->actionClassName = $this->getMockClass('Crud\Action\BaseAction', array('_handle'));
		$this->ActionClass = new $this->actionClassName($this->Controller);
		$this->_configureAction($this->ActionClass);
	}

	public function tearDown() {
		parent::tearDown();
		unset(
			$this->Crud,
			$this->Request,
			$this->Registry,
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

		$ActionClass = new $this->actionClassName($this->Controller, $expected);
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
		$expected = ['Crud.beforeRender' => [['callable' => [$this->ActionClass, 'publishSuccess']]]];
		$actual = $this->ActionClass->implementedEvents();
		$this->assertEquals($expected, $actual, 'The CrudAction implements events');
	}

/**
 * Test that an enabled action will call _handle
 *
 * @return void
 */
	public function testEnabledActionWorks() {
		$Request = $this->getMock('Cake\Network\Request', array('method'));
		$Request->action = 'add';
		$Request
			->expects($this->once())
			->method('method')
			->will($this->returnValue('GET'));

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_request', '_get'],
			[$this->Controller]
		);
		$Action
			->expects($this->any())
			->method('_request')
			->with()
			->will($this->returnValue($Request));
		$Action
			->expects($this->once())
			->method('_get', '_handle was never called on a enabled action')
			->will($this->returnValue(true));

		$this->_configureAction($Action);
		$Action->config('action', 'add');

		$expected = true;
		$actual = $Action->config('enabled');
		$this->assertSame($expected, $actual, 'The action is not enabled by default');

		$expected = true;
		$actual = $Action->handle();
		$this->assertSame($expected, $actual, 'Calling handle on a disabled action did not return null');
	}

/**
 * testDisable
 *
 * Test that calling disable() on the action object
 * disables the action and makes the handle method return false
 *
 * @return void
 */
	public function testDisable() {
		$Controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('foo'))
			->disableOriginalConstructor()
			->getMock();
		$Controller->methods = array('add', 'index', 'delete');

		$i = 0;

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_controller', '_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->at($i++))
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', false);
		$Action
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('action')
			->will($this->returnValue('add'));

		$Action->disable();

		$actual = array_search('add', $Controller->methods);
		$this->assertFalse($actual, '"add" was not removed from the controller::$methods array');
	}

/**
 * testEnable
 *
 * Test that calling enable() on the action object
 * enables the action
 *
 * @return void
 */
	public function testEnable() {
		$i = 0;

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_controller', '_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->at($i++))
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', true);
		$Action
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($this->Controller));
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('action')
			->will($this->returnValue('add'));

		$Action->enable();

		$actual = array_search('add', $this->Controller->methods);
		$this->assertTrue($actual !== false, '"add" was not added to the controller::$methods array');
	}

/**
 * Test that getting the findMethod will execute config()
 *
 * @return void
 */
	public function testFindMethodGet() {
		$this->markTestSkipped('BaseAction::findMethod() is removed');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->once())
			->method('config')
			->with('findMethod');

		$Action->findMethod();
	}

/**
 * Test that setting the findMethod will execute config()
 *
 * @return void
 */
	public function testFindMethodSet() {
		$this->markTestSkipped('BaseAction::findMethod() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->once())
			->method('config')
			->with('findMethod', 'my_first');

		$Action->findMethod('my_first');
	}

/**
 * Test that getting the saveMethod will execute config()
 *
 * @return void
 */
	public function testSaveMethodGet() {
		$this->markTestSkipped('BaseAction::saveMethod() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->once())
			->method('config')
			->with('saveMethod');

		$Action->saveMethod();
	}

/**
 * Test that setting the saveMethod will execute config()
 *
 * @return void
 */
	public function testSaveMethodSet() {
		$this->markTestSkipped('BaseAction::saveMethod() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
		$Action
			->expects($this->once())
			->method('config')
			->with('saveMethod', 'my_first');

		$Action->saveMethod('my_first');
	}

/**
 * Test that getting the saveOptions will execute config()
 *
 * @return void
 */
	public function testSaveOptionsGet() {
		$this->markTestSkipped('BaseAction::saveOptions() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
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
		$this->markTestSkipped('BaseAction::saveOptions() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
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
		$this->markTestSkipped('BaseAction::view() doesn\'t exist');

		$this->Request->action = 'add';

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
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
		$this->markTestSkipped('BaseAction::view() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
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
		$this->markTestSkipped('BaseAction::view() doesn\'t exist');

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', 'config'],
			[$this->Controller]
		);
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
		$this->markTestSkipped('Still have to fix test for setFlash()');

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

		$this->Subject->crud = $this->getMock('CrudComponent', array('trigger'), array($this->Registry));
		$this->Subject->crud
			->expects($this->once())
			->method('trigger')
			->with('setFlash', $data)
			->will($this->returnValue($object));

		$this->Subject->crud->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Registry));
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
		$this->markTestSkipped('BaseAction::detectPrimaryKeyFieldType() doesn\'t exist');

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
		$this->markTestSkipped();

		$CrudAction = $this->ActionClass;

		$expected = array(
			'validate' => 'first',
			'atomic' => true
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEquals($expected, $actual);

		$CrudAction->config('saveOptions.atomic', true);
		$expected = array(
			'validate' => 'first',
			'atomic' => true
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEquals($expected, $actual);

		$CrudAction->config('saveOptions', array(
			'fieldList' => array('hello')
		));
		$expected = array(
			'validate' => 'first',
			'atomic' => true,
			'fieldList' => array('hello')
		);
		$actual = $CrudAction->config('saveOptions');
		$this->assertEquals($expected, $actual);
	}

/**
 * Test that defining specific action configuration for saveAll takes
 * precedence over default configurations
 *
 * @return void
 */
	public function testGetSaveAllOptionsCustomAction() {
		$this->markTestSkipped();

		$expected = array('validate' => 'first', 'atomic' => true);
		$actual = $this->ActionClass->saveOptions();
		$this->assertEquals($expected, $actual);

		$this->ActionClass->saveOptions(array('atomic' => false));
		$expected = array('validate' => 'first', 'atomic' => false);
		$actual = $this->ActionClass->saveOptions();
		$this->assertEquals($expected, $actual);
	}

/**
 * testEmptyMessage
 *
 * @expectedException Exception
 * @expectedExceptionMessage Missing message type
 */
	public function testEmptyMessage() {
		$this->ActionClass->message(null);
	}

/**
 * testUndefinedMessage
 *
 * @expectedException Exception
 * @expectedExceptionMessage Invalid message type "not defined"
 */
	public function testUndefinedMessage() {
		$this->markTestSkipped();

		$this->ActionClass->message('not defined');
	}

/**
 * testBadMessageConfig
 *
 * @expectedException Exception
 * @expectedExceptionMessage Invalid message config for "badConfig" no text key found
 */
	public function testBadMessageConfig() {
		$this->markTestSkipped();

		$this->Crud->config('messages.badConfig', array('foo' => 'bar'));
		$this->ActionClass->message('badConfig');
	}

/**
 * testInheritedSimpleMessage
 *
 * @return void
 */
	public function testInheritedSimpleMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testOverridenSimpleMessage
 *
 * @return void
 */
	public function testOverridenSimpleMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testSimpleMessage
 *
 * @return void
 */
	public function testSimpleMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testSimpleMessageWithPlaceholders
 *
 * @return void
 */
	public function testSimpleMessageWithPlaceholders() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testInvalidIdMessage
 *
 * @return void
 */
	public function testInvalidIdMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testMessageNotFound
 *
 * @return void
 */
	public function testRecordNotFoundMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testBadRequestMethodMessage
 *
 * @return void
 */
	public function testBadRequestMethodMessage() {
		$this->markTestSkipped();

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
		$this->assertEquals($expected, $actual);
	}

/**
 * testHandle
 *
 * Test that calling handle will invoke _handle
 * when the action is enabbled
 *
 * @return void
 */
	public function testHandle() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', '_get', '_request'))
			->getMock();

		$Request = $this->getMock('CakeRequest', array('method'));
		$Request
			->expects($this->once())
			->method('method')
			->will($this->returnValue('GET'));

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->at($i++))
			->method('_get');

		$Action->handle(new Subject(array('args' => array())));
	}

/**
 * testHandleDisabled
 *
 * Test that calling handle will not invoke _handle
 * when the action is disabled
 *
 * @return void
 */
	public function testHandleDisabled() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', '_handle'))
			->getMock();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('_handle');

		$Action->handle(new Subject(array('args' => array())));
	}

/**
 * testGenericHandle
 *
 * Test that calling handle will invoke _handle
 * when the requestType handler is not available
 *
 * @return void
 */
	public function testGenericHandle() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', '_handle', '_request'))
			->getMock();

		$Request = $this->getMock('CakeRequest', array('method'));
		$Request
			->expects($this->once())
			->method('method')
			->will($this->returnValue('GET'));

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->once())
			->method('_handle');

		$Action->handle(new Subject(array('args' => array())));
	}

/**
 * testHandleException
 *
 * Test that calling handle will not invoke _handle
 * when the action is disabled
 *
 * @expectedException Cake\Network\Exception\NotImplementedException
 * @return void
 */
	public function testHandleException() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', '_request'))
			->getMock();

		$Request = $this->getMock('CakeRequest', array('method'));
		$Request
			->expects($this->once())
			->method('method')
			->will($this->returnValue('GET'));

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));

		$Action->handle(new Subject(array('args' => array())));
	}

/**
 * testValidateIdFalse
 *
 * If validateId is false - don't do squat
 *
 * @return void
 */
	public function testValidateIdFalse() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', 'detectPrimaryKeyFieldType'))
			->getMock();

		$Action
			->expects($this->once())
			->method('config')
			->with('validateId')
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('detectPrimaryKeyFieldType');

		$this->setReflectionClassInstance($Action);
		$return = $this->callProtectedMethod('_validateId', array('some id'), $Action);

		$this->assertTrue($return, 'If validateId is false the check should be skipped');
	}

/**
 * Test that getting the saveMethod will execute config()
 *
 * @return void
 */
	public function testRelatedModelsGet() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->setConstructorArgs(array($this->Subject))
			->getMock();
		$Action
			->expects($this->once())
			->method('config')
			->with('relatedModels');

		$Action->relatedModels();
	}

/**
 * Test that setting the saveMethod will execute config()
 *
 * @return void
 */
	public function testRelatedModelsSet() {
		$this->markTestSkipped();

		$Action = $this
			->getMockBuilder('CrudAction')
			->setMethods(array('config'))
			->setConstructorArgs(array($this->Subject))
			->getMock();
		$Action
			->expects($this->once())
			->method('config')
			->with('relatedModels', 'Tag', false);

		$Action->relatedModels('Tag');
	}

}
