<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('ViewCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudTestCase', 'Crud.Test/Support');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ViewCrudActionTest extends CrudTestCase {

/**
 * test_handleGet
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 *
 * The id provided, it's correct and it's in the db
 *
 * @covers ViewCrudAction::_handle
 * @return void
 */
	public function test_handleGet() {
		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$i = 0;

		$Action = $this
    	->getMockBuilder('ViewCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
      		'_validateId', '_controller', '_model',
      		'_trigger', 'viewVar', '_getFindMethod'
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
			->with('first')
			->will($this->returnValue('first'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query,
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('first', $query)
			->will($this->returnValue($data));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterFind', array(
				'id' => 1,
				'item' => $data
			))
			->will($this->returnValue(new CrudSubject(array('item' => $data))));
		$Action
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Action
			->expects($this->at($i++))
			->method('viewVar')
			->with()
			->will($this->returnValue('example'));
		$Controller
			->expects($this->once())
			->method('set')
			->with(array('example' => $data, 'success' => true));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeRender');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleGetCustomViewVar
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * Testing that setting a different viewVar actually works
 *
 * @covers ViewCrudAction::_handle
 * @return void
 */
	public function test_handleGetCustomViewVar() {
		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$i = 0;

		$Action = $this
    	->getMockBuilder('ViewCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
      		'_validateId', '_controller', '_model',
      		'_trigger', 'viewVar', '_getFindMethod'
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
			->with('first')
			->will($this->returnValue('first'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query,
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('first', $query)
			->will($this->returnValue($data));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('afterFind', array(
				'id' => 1,
				'item' => $data
			))
			->will($this->returnValue(new CrudSubject(array('item' => $data))));
		$Action
			->expects($this->at($i++))
			->method('_controller')
			->with()
			->will($this->returnValue($Controller));
		$Action
			->expects($this->at($i++))
			->method('viewVar')
			->with()
			->will($this->returnValue('item'));
		$Controller
			->expects($this->once())
			->method('set')
			->with(array('item' => $data, 'success' => true));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeRender');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleGetNotFound
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * The ID provided is valid, but do not exist in the database
 *
 * @covers ViewCrudAction::_handle
 * @expectedException NotFoundException
 * @exepctedExceptionMessage Not Found
 * @exepctedExceptionCode 404
 * @return void
 */
	public function test_handleGetNotFound() {
		$query = array('conditions' => array('Model.id' => 1));
		$data = array('Model' => array('id' => 1));

		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('escapeField', 'find'))
			->getMock();

		$Controller = $this
			->getMockBuilder('Controller')
			->disableOriginalConstructor()
			->setMethods(array('set'))
			->getMock();

		$i = 0;

		$Action = $this
    	->getMockBuilder('ViewCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array(
      		'_validateId', '_controller', '_model',
      		'_trigger', 'viewVar', '_getFindMethod',
      		'message'
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
			->with('first')
			->will($this->returnValue('first'));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('beforeFind', array(
				'findMethod' => 'first',
				'query' => $query,
				'id' => 1
			))
			->will($this->returnValue(new CrudSubject(array('query' => $query, 'findMethod' => 'first'))));
		$Model
			->expects($this->once())
			->method('find')
			->with('first', $query)
			->will($this->returnValue(false));
		$Action
			->expects($this->at($i++))
			->method('_trigger')
			->with('recordNotFound', array('id' => 1));
		$Action
			->expects($this->at($i++))
			->method('message')
			->with('recordNotFound', array('id' => 1))
			->will($this->returnValue(array('class' => 'NotFoundException', 'text' => 'NotFound', 'code' => 404)));
		$Action
			->expects($this->never())
			->method('_controller');
		$Action
			->expects($this->never())
			->method('viewVar');
		$Controller
			->expects($this->never())
			->method('set');

    $this->setReflectionClassInstance($Action);
    $this->callProtectedMethod('_handle', array(1), $Action);
	}

/**
 * test_handleGetInvalidId
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes that the id for the view
 * action does not exist in the database
 *
 * @covers ViewCrudAction::_handle
 * @return void
 */
	public function test_handleGetInvalidId() {
		$Action = $this
    	->getMockBuilder('ViewCrudAction')
      ->disableOriginalConstructor()
      ->setMethods(array('_validateId', '_model', 'beforeRender', '_trigger'))
      ->getMock();
		$Action
      ->expects($this->once())
      ->method('_validateId')
      ->with(1)
      ->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('_model');
		$Action
			->expects($this->never())
			->method('_trigger');

    $this->setReflectionClassInstance($Action);
    $result = $this->callProtectedMethod('_handle', array(1), $Action);
    $this->assertFalse($result);
	}

}
