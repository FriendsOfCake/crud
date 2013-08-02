<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('DeleteCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudTestCase', 'Crud.Test/Support');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class DeleteCrudActionTest extends CrudTestCase {

/**
 * Test that calling handle will invoke _handle
 *
 * @covers CrudAction::handle
 * @return void
 */
	public function testThatCrudActionWillHandle() {
		$Request = new CakeRequest();
		$Request->params = array('action' => 'delete');

		$Action = $this
			->getMockBuilder('AddCrudAction')
			->disableOriginalConstructor()
			->setMethods(array('config', '_request', 'enforceRequestType', '_handle'))
			->getMock();

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
			->method('config')
			->with('action')
			->will($this->returnValue('delete'));
		$Action
			->expects($this->at($i++))
			->method('enforceRequestType');
		$Action
			->expects($this->at($i++))
			->method('_handle');

		$Action->handle(new CrudSubject(array('args' => array())));
	}

/**
 * test_handle
 *
 * test the best-case flow
 *
 * @covers DeleteCrudAction::_handle
 * @return void
 */
	public function test_handle() {
		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'delete'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('referer'))
			->getMock();

		$query = array('conditions' => array('Model.id' => 1));

		$CrudSubject = new CrudSubject();

		$i = 0;

		$Action = $this
			->getMockBuilder('DeleteCrudAction')
			->disableOriginalConstructor()
			->setMethods(array(
				'_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect'
			))
			->getMock();
		$Action
			->expects($this->at($i++))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->with()
			->will($this->returnValue($Model));
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Action
			->expects($this->at($i++))
			->method('_getFindMethod')
			->with('count')
			->will($this->returnValue('count'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array('id' => 1, 'query' => $query, 'findMethod' => 'count'))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'count'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('count', $query)
			->will($this->returnValue(1));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeDelete', array('id' => 1))
			->will($this->returnValue(new CrudSubject(array('stopped' => false))));
		$Model
			->expects($this->once())
			->method('delete')
			->with()
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('setFlash')
			->with('success');
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterDelete', array('id' => 1, 'success' => true))
			->will($this->returnValue($CrudSubject));
		$Action
			->expects($this->once())
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->once())
			->method('referer')
			->with(array('action' => 'index'))
			->will($this->returnValue(array('action' => 'index')));
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleNotFound
 *
 * Test the behavior when a record is not found in the database
 *
 * @covers DeleteCrudAction::_handle
 * @expectedException NotFoundException
 * @expectedExceptionMessage Not Found
 * @expectedExceptionCode 404
 * @return void
 */
	public function test_handleNotFound() {
		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'delete'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('referer'))
			->getMock();

		$query = array('conditions' => array('Model.id' => 1));

		$CrudSubject = new CrudSubject();

		$i = 0;

		$Action = $this
			->getMockBuilder('DeleteCrudAction')
			->disableOriginalConstructor()
			->setMethods(array(
				'_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect', 'message'
			))
			->getMock();
		$Action
			->expects($this->at($i++))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->with()
			->will($this->returnValue($Model));
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Action
			->expects($this->at($i++))
			->method('_getFindMethod')
			->with('count')
			->will($this->returnValue('count'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array('id' => 1, 'query' => $query, 'findMethod' => 'count'))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'count'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('count', $query)
			->will($this->returnValue(0));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('recordNotFound', array('id' => 1));
		$Action
			->expects($this->at($i++))
			->method('message')
			->with('recordNotFound', array('id' => 1))
			->will($this->returnValue(array('class' => 'NotFoundException', 'text' => 'Not Found', 'code' => 404)));
		$Action
			->expects($this->never())
			->method('_trigger');
		$Model
			->expects($this->never())
			->method('delete');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleDeleteFailed
 *
 * test the behavior of delete() failing
 *
 * @covers DeleteCrudAction::_handle
 * @return void
 */
	public function test_handleDeleteFailed() {
		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'delete'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('referer'))
			->getMock();

		$query = array('conditions' => array('Model.id' => 1));

		$CrudSubject = new CrudSubject();

		$i = 0;

		$Action = $this
			->getMockBuilder('DeleteCrudAction')
			->disableOriginalConstructor()
			->setMethods(array(
				'_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect'
			))
			->getMock();
		$Action
			->expects($this->at($i++))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->with()
			->will($this->returnValue($Model));
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Action
			->expects($this->at($i++))
			->method('_getFindMethod')
			->with('count')
			->will($this->returnValue('count'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array('id' => 1, 'query' => $query, 'findMethod' => 'count'))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'count'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('count', $query)
			->will($this->returnValue(1));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeDelete', array('id' => 1))
			->will($this->returnValue(new CrudSubject(array('stopped' => false))));
		$Model
			->expects($this->once())
			->method('delete')
			->with()
			->will($this->returnValue(false));
		$Action
			->expects($this->at($i++))
			->method('setFlash')
			->with('error');
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterDelete', array('id' => 1, 'success' => false))
			->will($this->returnValue($CrudSubject));
		$Action
			->expects($this->once())
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->once())
			->method('referer')
			->with(array('action' => 'index'))
			->will($this->returnValue(array('action' => 'index')));
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleDeleteStoppedByEvent
 *
 * test the behavior when the beforeDelete callback
 * stops the event
 *
 * @covers DeleteCrudAction::_handle
 * @return void
 */
	public function test_handleDeleteStoppedByEvent() {
		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find', 'delete'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('referer'))
			->getMock();

		$query = array('conditions' => array('Model.id' => 1));

		$CrudSubject = new CrudSubject();

		$i = 0;

		$Action = $this
			->getMockBuilder('DeleteCrudAction')
			->disableOriginalConstructor()
			->setMethods(array(
				'_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect'
			))
			->getMock();
		$Action
			->expects($this->at($i++))
			->method('_validateId')
			->with(1)
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_request')
			->with()
			->will($this->returnValue($Request));
		$Request
			->expects($this->once())
			->method('is')
			->with('delete')
			->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_model')
			->with()
			->will($this->returnValue($Model));
		$Model
			->expects($this->once())
			->method('escapeField')
			->with()
			->will($this->returnValue('Model.id'));
		$Action
			->expects($this->at($i++))
			->method('_getFindMethod')
			->with('count')
			->will($this->returnValue('count'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array('id' => 1, 'query' => $query, 'findMethod' => 'count'))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'count'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('count', $query)
			->will($this->returnValue(1));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeDelete', array('id' => 1))
			->will($this->returnValue(new CrudSubject(array('stopped' => true))));
		$Model
			->expects($this->never())
			->method('delete');
		$Action
			->expects($this->at($i++))
			->method('setFlash')
			->with('error');
		$Action
			->expects($this->once())
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->once())
			->method('referer')
			->with(array('action' => 'index'))
			->will($this->returnValue(array('action' => 'index')));
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleInvalidId
 *
 * Test the behavior when the ID is invalid
 *
 * @covers DeleteCrudAction::_handle
 * @return void
 */
	public function test_handleInvalidId() {
		$Action = $this
			->getMockBuilder('DeleteCrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_model', '_validateId'))
			->getMock();
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1)
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('_model');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(1), $Action);
	}

}
