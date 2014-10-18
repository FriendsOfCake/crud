<?php
namespace Crud\TestCase\Action;

use Cake\ORM\TableRegistry;
use Crud\Event\Subject;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class BaseActionTest extends TestCase {

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
		$this->Controller->Crud = $this->Crud;
		$this->Controller->modelClass = 'CrudExamples';
		$this->Controller->CrudExamples = \Cake\ORM\TableRegistry::get('Crud.CrudExamples');
		$this->Controller->CrudExamples->alias('MyModel');

		$this->actionClassName = $this->getMockClass('Crud\Action\BaseAction', array('_handle'));
		$this->ActionClass = new $this->actionClassName($this->Controller);
		$this->_configureAction($this->ActionClass);
	}

	public function tearDown() {
		parent::tearDown();
		TableRegistry::clear();
		unset(
			$this->Crud,
			$this->Request,
			$this->Registry,
			$this->Controller,
			$this->ActionClass
		);
	}

	protected function _configureAction($action) {
		$action->config(array(
			'action' => 'add',
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
 * disables the action
 *
 * @return void
 */
	public function testDisable() {
		$i = 0;

		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['config'],
			[$this->Controller]
		);
		$Action
			->expects($this->at($i++))
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', false);

		$Action->disable();
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
			['config'],
			[$this->Controller]
		);
		$Action
			->expects($this->at($i++))
			->method('config', 'enabled was not changed to false by config()')
			->with('enabled', true);

		$Action->enable();
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
				'original' => 'Ahoy'
			),
			'key' => 'custom',
			'type' => 'add.success',
			'name' => 'test',
			'text' => 'Ahoy',
		);

		$Subject = new Subject();

		$this->Controller->Crud = $this->getMock(
			'Crud\Controller\Component\CrudComponent',
			array('trigger'),
			array($this->Registry)
		);
		$this->Controller->Crud
			->expects($this->once())
			->method('trigger')
			->with('setFlash', $Subject)
			->will($this->returnValue(new \Cake\Event\Event('Crud.setFlash')));

		$this->Controller->Flash = $this->getMock(
			'Cake\Controller\Component\FlashComponent',
			array('set'),
			array($this->Registry)
		);
		$this->Controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				$data['text'],
				[
					'element' => $data['element'],
					'params' => $data['params'],
					'key' => $data['key'],
				]
			);

		$this->ActionClass->config('name', 'test');
		$this->ActionClass->config('messages', [
			'success' => ['text' => 'Ahoy', 'key' => 'custom']
		]);
		$this->ActionClass->setFlash('success', $Subject);
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
		$this->ActionClass->message('not defined');
	}

/**
 * testBadMessageConfig
 *
 * @expectedException Exception
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
			'name' => 'my model',
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
			'name' => 'my model',
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
		$this->ActionClass->config('messages.simple', 'Simple message');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Simple message'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => 'my model',
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
		$this->Crud->config('messages.simple', 'Simple message with id "{id}"');

		$expected = array(
			'element' => 'default',
			'params' => array(
				'class' => 'message simple',
				'original' => 'Simple message with id "{id}"'
			),
			'key' => 'flash',
			'type' => 'add.simple',
			'name' => 'my model',
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
		$expected = array(
			'code' => 400,
			'class' => 'Cake\Network\Exception\BadRequestException',
			'element' => 'default',
			'params' => array(
				'class' => 'message invalidId',
				'original' => 'Invalid id'
			),
			'key' => 'flash',
			'type' => 'add.invalidId',
			'name' => 'my model',
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
		$expected = array(
			'code' => 404,
			'class' => 'Cake\Network\Exception\NotFoundException',
			'element' => 'default',
			'params' => array(
				'class' => 'message recordNotFound',
				'original' => 'Not found'
			),
			'key' => 'flash',
			'type' => 'add.recordNotFound',
			'name' => 'my model',
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
		$expected = array(
			'code' => 405,
			'class' => 'Cake\Network\Exception\MethodNotAllowedException',
			'element' => 'default',
			'params' => array(
				'class' => 'message badRequestMethod',
				'original' => 'Method not allowed. This action permits only {methods}'
			),
			'key' => 'flash',
			'type' => 'add.badRequestMethod',
			'name' => 'my model',
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
		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_get', '_request', 'config'],
			[$this->Controller]
		);

		$Request = $this->getMock('Cake\Network\Request', array('method'));
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

		$Action->handle();
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
		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_get', 'config'],
			[$this->Controller]
		);

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('config')
			->with('enabled')
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('_handle');

		$Action->handle();
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
		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_handle', '_request', 'config'],
			[$this->Controller]
		);

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

		$Action->handle();
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
		$Action = $this->getMock(
			'Crud\Action\BaseAction',
			['_request', 'config'],
			[$this->Controller]
		);

		$Request = $this->getMock('Cake\Network\Request', array('method'));
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

		$Action->handle();
	}

}
