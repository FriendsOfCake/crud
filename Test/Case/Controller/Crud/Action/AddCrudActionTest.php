<?php

App::uses('Model', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CrudTestCase', 'Crud.Test/Support');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('AddCrudAction', 'Crud.Controller/Crud/Action');

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddCrudActionTest extends CrudTestCase {

/**
 * Test the normal HTTP GET flow of _handle
 *
 * @covers AddCrudAction::_handle
 * @return void
 */
	public function testActionGet() {
		$Request = $this->getMock('CakeRequest', array('is'));
		$Request
			->expects($this->once())
			->method('is')
			->with('post')
			->will($this->returnValue(false));

		$Model = $this->getMock('Model', array('create'));
		$Model
			->expects($this->once())
			->method('create');

		$Action = $this
			->getMockBuilder('AddCrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_request', '_model', '_trigger'))
			->getMock();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($Model));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeRender', array('success' => false));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(), $Action);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on success
 *
 * @covers AddCrudAction::_handle
 * @return void
 */
	public function testActionPostSuccess() {
		$Request = $this->getMock('CakeRequest', array('is'));
		$Request->data = array('Post' => array('name' => 'Hello World'));
		$Request
			->expects($this->once())
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model = $this->getMock('Model', array('saveAll'));
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnCallback(function() use ($Model) {
				$Model->id = 1;
				return true;
			}));

		$Action = $this
			->getMockBuilder('AddCrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_request', '_model', '_trigger', 'setFlash', '_redirect'))
			->getMock();

		$AfterSaveSubject = new CrudSubject();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($Model));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeSave');
		$Action
			->expects($this->at($i++))
			->method('setFlash')
			->with('success');
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterSave', array('success' => true, 'created' => true, 'id' => 1))
			->will($this->returnValue($AfterSaveSubject));
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($AfterSaveSubject, array('action' => 'index'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(), $Action);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on error
 *
 * @covers AddCrudAction::_handle
 * @return void
 */
	public function testActionPostError() {
		$Request = $this->getMock('CakeRequest', array('is'));
		$Request->data = array('Post' => array('name' => 'Hello World'));
		$Request
			->expects($this->once())
			->method('is')
			->with('post')
			->will($this->returnValue(true));

		$Model = $this->getMock('Model', array('saveAll'));
		$Model->data = array('model' => true);
		$Model
			->expects($this->once())
			->method('saveAll')
			->with($Request->data)
			->will($this->returnValue(false));

		$Action = $this
			->getMockBuilder('AddCrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_request', '_model', '_trigger', 'setFlash'))
			->getMock();

		$AfterSaveSubject = new CrudSubject();

		$i = 0;
		$Action
			->expects($this->at($i++))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($Model));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeSave');
		$Action
			->expects($this->at($i++))
			->method('setFlash')
			->with('error');
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterSave', array('success' => false, 'created' =>false))
			->will($this->returnValue($AfterSaveSubject));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeRender', array('success' => false));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(), $Action);

		$result = $Request->data;
		$expected = $Request->data;
		$expected['model'] = true;
		$this->assertEqual($result, $expected, 'The Request::$data and Model::$data was not merged');
	}

}
