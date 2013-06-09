<?php

App::uses('Model', 'Model');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeEvent', 'Event');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('IndexCrudAction', 'Crud.Controller/Crud/Action');
App::uses('FieldFilterListener', 'Crud.Controller/Crud/Listener');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class FieldFilterListenerTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ControllerMock = $this->getMockBuilder('Controller');
		$this->RequestMock = $this->getMockBuilder('CakeRequest');
		$this->CrudMock = $this->getMockBuilder('CrudComponent');
		$this->ActionMock = $this->getMockBuilder('IndexCrudAction');
	}

	public function tearDown() {
		parent::tearDown();

		unset(
			$this->ModelMock,
			$this->Controller,
			$this->RequestMock,
			$this->CrudMock,
			$this->ActionMock
		);
	}

/**
 * Helper method to generate and mock all the required
 * classes
 *
 * `$hasField` is a field => bool array with what
 * fields should exist according to 'hasField' model check
 *
 * @param array $hasField
 * @return array
 */
	protected function _mockClasses($hasField = array()) {
		$CrudSubject = new CrudSubject();

		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('action'))
			->getMock();

		$Model = $this->ModelMock
			->setConstructorArgs(array(
				array('table' => 'models', 'name' => 'Model', 'ds' => 'test')
			))
			->setMethods(array('hasField'))
			->getMock();
		$Model->alias = 'Model';

		$Controller = $this->ControllerMock
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Controller->Components = new StdClass;

		$Request = new CakeRequest();

		$CrudSubject->set(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => $Controller,
			'action' => 'view',
			'action' => 'view',
			'model' => $Model,
			'modelClass' => $Model->name,
			'args' => array(),
			'query' => array(
				'fields' => null,
				'contain' => null
			)
		));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(null)
			->getMock();

		$Listener = new FieldFilterListener($CrudSubject);
		$Event = new CakeEvent('Test', $CrudSubject);

		$Crud
			->expects($this->any())
			->method('action')
			->will($this->returnValue($Action));

		$i = 0;
		foreach ($hasField as $field => $has) {
			$Model
				->expects($this->at($i))
				->method('hasField')
				->with($field)
				->will($this->returnValue($has));

			$i++;
		}

		return compact('Crud', 'Model', 'Controller', 'Request', 'CrudSubject', 'Listener', 'Action', 'Event');
	}

/**
 * Test that a beforeFind with no fields in the query
 * will not inject any fields or contain into the query
 *
 * @return void
 */
	public function testRequestWithoutFields() {
		extract($this->_mockClasses());

		$Listener->beforeFind($Event);

		$this->assertNull($CrudSubject->query['fields']);
	}

/**
 * Test that a beforeFind with 3 fields
 * will inject them into the fields array
 *
 * @return void
 */
	public function testRequestWithFields() {
		$hasField = array('id' => true,	'name' => true,	'password' => true);
		extract($this->_mockClasses($hasField));
		$Request->query['fields'] = 'id,name,password';

		$Listener->beforeFind($Event);

		$expected = array('Model.id', 'Model.name', 'Model.password');
		$actual = $CrudSubject->query['fields'];
		$this->assertSame($expected, $actual);
	}

/**
 * Test that a beforeFind with 3 fields
 * will inject two into the fields array
 * since they exist in the model, but the 3rd
 * field (password) will be removed
 *
 * @return void
 */
	public function testGetFieldsIncludeFieldNotInModel() {
		$hasField = array('id' => true,	'name' => true,	'password' => false);
		extract($this->_mockClasses($hasField));
		$Request->query['fields'] = 'id,name,password';

		$Listener->beforeFind($Event);

		$expected = array('Model.id', 'Model.name');
		$actual = $CrudSubject->query['fields'];
		$this->assertSame($expected, $actual);
	}

/**
 * Test that whitelisting only will allow
 * fields in the whitelist to be included
 * in the fieldlist
 *
 * Password exist as a column, but is not
 * whitelisted, and thus should be removed
 *
 * @return void
 */
	public function testWhitelistFields() {
		$hasField = array('id' => true,	'name' => true,	'password' => true);
		extract($this->_mockClasses($hasField));
		$Request->query['fields'] = 'id,name,password';

		$Listener->whitelistfields(array('Model.id', 'Model.name'));

		$Listener->beforeFind($Event);

		$expected = array('Model.id', 'Model.name');
		$actual = $CrudSubject->query['fields'];
		$this->assertSame($expected, $actual);
	}

/**
 * Test that blacklisting a field will ensure
 * that it will be removed from list of fields
 *
 * Password exist as a column, but is
 * blacklisted, and thus should be removed
 *
 * @return void
 */
	public function testBlacklistFields() {
		$hasField = array('id' => true,	'name' => true,	'password' => true);
		extract($this->_mockClasses($hasField));
		$Request->query['fields'] = 'id,name,password';

		$Listener->blacklistFields(array('Model.password'));

		$Listener->beforeFind($Event);

		$expected = array('Model.id', 'Model.name');
		$actual = $CrudSubject->query['fields'];
		$this->assertSame($expected, $actual);
	}

}
