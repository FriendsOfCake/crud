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
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteCrudActionTest extends CrudTestCase {

/**
 * testDelete
 *
 * test the best-case flow
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
	public function testDeleteOnDelete() {
    $Action = $this->_actionSuccess();
		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_delete', array(1), $Action);
	}

/**
 * testDelete
 *
 * test the best-case flow
 *
 * @covers DeleteCrudAction::_post
 * @return void
 */
	public function testDeleteOnPost() {
    $Action = $this->_actionSuccess();
		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_post', array(1), $Action);
	}

	protected function _actionSuccess() {
		$Request = $this->getMock('CakeRequest');

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
				'_request', '_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect', '_controller'
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
			->expects($this->at($i++))
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
		return $Action;
	}

/**
 * testActiondeleteWithAddRedirect
 *
 * test the best-case flow with add redirection
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
	public function testActiondeleteWithAddRedirect() {
    $data = array(
      '_edit' => '_edit',
    );

		$Request = $this->getMock('CakeRequest');
    $Request->setMethods(array('data'));
    $Request->data = $data;

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
				'_request', '_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect', '_controller'
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
    $Request
      ->expects($this->at(1))
      ->method('data')
      ->with('_add')
      ->will($this->returnValue(true));
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'add'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_delete', array(1), $Action);
	}

/**
 * test_deleteNotFound
 *
 * Test the behavior when a record is not found in the database
 *
 * @covers DeleteCrudAction::_delete
 * @expectedException NotFoundException
 * @expectedExceptionMessage Not Found
 * @expectedExceptionCode 404
 * @return void
 */
	public function test_deleteNotFound() {
		$Request = $this->getMock('CakeRequest');

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
				'_request', '_model', '_validateId', '_getFindMethod',
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
			->method('_request')
			->with()
			->will($this->returnValue($Request));
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
		$Model
			->expects($this->never())
			->method('delete');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_delete', array(1), $Action);
	}

/**
 * test_deleteDeleteFailed
 *
 * test the behavior of delete() failing
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
	public function test_deleteDeleteFailed() {
		$Request = $this->getMock('CakeRequest');

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
				'_request', '_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect', '_controller'
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
			->expects($this->at($i++))
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
		$this->callProtectedMethod('_delete', array(1), $Action);
	}

/**
 * test_deleteDeleteStoppedByEvent
 *
 * test the behavior when the beforeDelete callback
 * stops the event
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
	public function test_deleteDeleteStoppedByEvent() {
		$Request = $this->getMock('CakeRequest');

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
				'_request', '_model', '_validateId', '_getFindMethod',
				'_trigger', 'setFlash', '_redirect', '_controller'
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
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->once())
			->method('referer')
			->with(array('action' => 'index'))
			->will($this->returnValue(array('action' => 'index')));
		$CrudSubject->stopped = true;
		$Action
			->expects($this->at($i++))
			->method('_redirect')
			->with($CrudSubject, array('action' => 'index'));

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_delete', array(1), $Action);
	}

/**
 * test_deleteInvalidId
 *
 * Test the behavior when the ID is invalid
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
	public function test_deleteInvalidId() {
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
		$this->callProtectedMethod('_delete', array(1), $Action);
	}


/**
 * Test that calling HTTP DELETE on an edit action
 * with `_cancel` set in the POST data will cancel the form submission
 *
 * @covers DeleteCrudAction::_delete
 * @return void
 */
  public function testDeleteActionCancel() {
    $data = array(
      '_cancel' => '_cancel',
      'Model' => array('id' => 1)
    );

    $CrudSubject = new CrudSubject();

    $Request = $this->getMock('CakeRequest');
    $Request->setMethods(array('data'));
    $Request->data = $data;

    $Controller = $this
      ->getMockBuilder('Controller')
      ->disableOriginalConstructor()
      ->setMethods(array('referer'))
      ->getMock();

    $Model = $this->getMock('Model');

    $i = 0;
    $Action = $this
      ->getMockBuilder('DeleteCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
        '_validateId', '_request', '_model', '_trigger',
        '_controller', '_redirect'
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
      ->will($this->returnValue($Request));
    $Action
      ->expects($this->at($i++))
      ->method('_model')
      ->will($this->returnValue($Model));
    $Request
      ->expects($this->at(0))
      ->method('data')
      ->with('_cancel')
      ->will($this->returnValue(true));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeCancel')
      ->will($this->returnValue($CrudSubject));
    $Action
      ->expects($this->at($i++))
      ->method('_controller')
      ->will($this->returnValue($Controller));
    $Controller
      ->expects($this->at(0))
      ->method('referer')
      ->with(array('action' => 'index'))
      ->will($this->returnValue(array('action' => 'index')));
    $Action
      ->expects($this->at($i++))
      ->method('_redirect')
      ->with($CrudSubject, array('action' => 'index'));

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_delete', array(1), $Action);
  }

}
