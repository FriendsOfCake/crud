<?php

App::uses('Model', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CrudTestCase', 'Crud.Test/Support');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('EditCrudAction', 'Crud.Controller/Crud/Action');

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditCrudActionTest extends CrudTestCase {

/**
 * Test the normal HTTP GET flow of _handle
 *
 * @covers EditCrudAction::_handle
 * @return void
 */
  public function testActionGet() {
    $query = array('conditions' => array('Model.id' => 1));
    $data = array('Model' => array('id' => 1));

    $Request = $this
      ->getMock('CakeRequest', array('is'));
    $Request
      ->expects($this->once())
      ->method('is')
      ->with('put')
      ->will($this->returnValue(false));

    $Model = $this
      ->getMock('Model', array('create', 'find', 'escapeField'));
    $Model
      ->expects($this->once())
      ->method('escapeField')
      ->with()
      ->will($this->returnValue('Model.id'));
    $Model
      ->expects($this->once())
      ->method('find')
      ->with('first', $query)
      ->will($this->returnValue($data));

    $i = 0;
    $Action = $this
      ->getMockBuilder('EditCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array('_validateId', '_request', '_model', '_trigger', '_getFindMethod'))
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
    $Action
      ->expects($this->at($i++))
      ->method('_getFindMethod')
      ->with('first')
      ->will($this->returnValue('first'));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeFind', array('findMethod' => 'first', 'query' => $query))
      ->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('afterFind', array('id' => 1, 'item' => $data))
      ->will($this->returnValue(new CrudSubject(array('item' => $data))));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeRender');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
  }

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the best possible case
 * The id provided, it's correct and it's in the db
 *
 * @covers EditCrudAction::_handle
 * @return void
 */
  public function testActionPut() {
    $data = array('Model' => array('id' => 1));

    $CrudSubject = new CrudSubject();

    $Request = $this
      ->getMock('CakeRequest', array('is'));
    $Request->data = $data;

    $Model = $this
      ->getMock('Model', array('saveAll'));

    $i = 0;
    $Action = $this
      ->getMockBuilder('EditCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
        '_validateId', '_request', '_model', '_trigger',
        '_redirect', 'setFlash', 'saveOptions'
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
      ->expects($this->once())
      ->method('is')
      ->with('put')
      ->will($this->returnValue(true));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeSave', array('id' => 1));
    $Action
      ->expects($this->at($i++))
      ->method('saveOptions')
      ->will($this->returnValue(array('atomic' => true)));
    $Model
      ->expects($this->once())
      ->method('saveAll')
      ->with($data)
      ->will($this->returnValue(true));
    $Action
      ->expects($this->at($i++))
      ->method('setFlash')
      ->with('success');
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('afterSave', array('success' => true, 'created' => false, 'id' => 1))
      ->will($this->returnValue($CrudSubject));
    $Action
      ->expects($this->at($i++))
      ->method('_redirect')
      ->with($CrudSubject, array('action' => 'index'));

    $Action
      ->expects($this->exactly(2))
      ->method('_trigger');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
  }

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the saveAll() call fails
 * The id provided, it's correct and it's in the db
 *
 * @covers EditCrudAction::_handle
 * @return void
 */
  public function testActionPutSaveError() {
    $data = array('Model' => array('id' => 1));

    $CrudSubject = new CrudSubject();

    $Request = $this
      ->getMock('CakeRequest', array('is'));
    $Request->data = $data;

    $Model = $this
      ->getMock('Model', array('saveAll'));

    $i = 0;
    $Action = $this
      ->getMockBuilder('EditCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
        '_validateId', '_request', '_model', '_trigger',
        '_redirect', 'setFlash', 'saveOptions'
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
      ->expects($this->once())
      ->method('is')
      ->with('put')
      ->will($this->returnValue(true));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeSave', array('id' => 1));
    $Action
      ->expects($this->at($i++))
      ->method('saveOptions')
      ->will($this->returnValue(array('atomic' => true)));
    $Model
      ->expects($this->once())
      ->method('saveAll')
      ->with($data)
      ->will($this->returnValue(false));
    $Action
      ->expects($this->at($i++))
      ->method('setFlash')
      ->with('error');
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('afterSave', array('success' => false, 'created' => false, 'id' => 1))
      ->will($this->returnValue($CrudSubject));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeRender');
    $Action
      ->expects($this->never())
      ->method('_redirect');
    $Action
      ->expects($this->exactly(3))
      ->method('_trigger');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
  }

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * Given an ID, we test what happens if the ID doesn't
 * exist in the database
 *
 * @covers EditCrudAction::_handle
 * @expectedException NotFoundException
 * @expectedExceptionMessage Not Found
 * @expectedExceptionCode 404
 * @return void
 */
  public function testActionGetWithNonexistingId() {
    $CrudSubject = new CrudSubject();

    $query = array('conditions' => array('Model.id' => 1));

    $Request = $this
      ->getMock('CakeRequest', array('is'));

    $Model = $this
      ->getMock('Model', array('escapeField', 'find'));

    $i = 0;
    $Action = $this
      ->getMockBuilder('EditCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
        '_validateId', '_request', '_model', '_trigger',
        '_redirect', 'setFlash', 'saveOptions', 'message'
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
      ->expects($this->once())
      ->method('is')
      ->with('put')
      ->will($this->returnValue(false));
    $Model
      ->expects($this->once())
      ->method('escapeField')
      ->with()
      ->will($this->returnValue('Model.id'));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeFind', array('findMethod' => 'first', 'query' => $query))
      ->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
    $Model
      ->expects($this->once())
      ->method('find')
      ->with('first', $query)
      ->will($this->returnValue(array()));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('recordNotFound', array('id' => 1))
      ->will($this->returnValue($CrudSubject));
    $Action
      ->expects($this->at($i++))
      ->method('message')
      ->with('recordNotFound', array('id' => 1))
      ->will($this->returnValue(array('class' => 'NotFoundException', 'text' => 'Not Found', 'code' => 404)));
    $Action
      ->expects($this->exactly(2))
      ->method('_trigger');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
  }

/**
 * Test that calling HTTP GET on an edit action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 *
 * The id provided, it's correct and it's in the db
 * Additionally the `_getFindMethod` method returns
 * something not-default
 *
 * @covers EditCrudAction::_handle
 * @return void
 */
  public function testGetWithCustomFindMethod() {
    $query = array('conditions' => array('Model.id' => 1));
    $data = array('Model' => array('id' => 1));

    $Request = $this
      ->getMock('CakeRequest', array('is'));

    $Model = $this
      ->getMock('Model', array('create', 'find', 'escapeField'));

    $i = 0;
    $Action = $this
      ->getMockBuilder('EditCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array('_validateId', '_request', '_model', '_trigger', '_getFindMethod'))
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
      ->expects($this->once())
      ->method('is')
      ->with('put')
      ->will($this->returnValue(false));
    $Model
      ->expects($this->once())
      ->method('escapeField')
      ->with()
      ->will($this->returnValue('Model.id'));
    $Action
      ->expects($this->at($i++))
      ->method('_getFindMethod')
      ->with('first')
      ->will($this->returnValue('first'));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeFind', array('findMethod' => 'first', 'query' => $query))
      ->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'myCustomQuery'))));
    $Model
      ->expects($this->once())
      ->method('find')
      ->with('myCustomQuery', $query)
      ->will($this->returnValue($data));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('afterFind', array('id' => 1, 'item' => $data))
      ->will($this->returnValue(new CrudSubject(array('item' => $data))));
    $Action
      ->expects($this->at($i++))
      ->method('_trigger')
      ->with('beforeRender');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
  }

}
