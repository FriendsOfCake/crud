<?php

App::uses('CakeEvent', 'Event');
App::uses('AddCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class AddCrudActionText extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		$this->Crud = $this->getMock('CrudComponent');
		$this->Request = $this->getMock('CakeRequest');
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Controller = $this->getMock('Controller');
		$this->handleAction = 'add';

		$this->Subject = new CrudSubject(array(
			'request' => $this->Request,
			'crud' => $this->Crud,
			'collection' => $this->Collection,
			'controller' => $this->Controller,
			'handleAction' => $this->handleAction
		));

		$this->ActionClass = new AddCrudAction($this->Subject);
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

/**
 * Test that it's possible to override all
 * configuration settings through the __constructor()
 *
 * @return void
 */
	public function testOverrideAllDefaults() {
		$config = array(
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
			)
		);

		$ActionClass = new AddCrudAction($this->Subject, $config);
		// This is injected by the CrudAction, not technically a setting
		$config['handleAction'] = 'add';

		$this->assertEquals($config, $ActionClass->config(), 'It was not possible to override all default settings.');
	}

/**
 * Test that we get the expected events
 *
 * @return void
 */
	public function testImplementedEvents() {
		$expected = array();
		$result = $this->ActionClass->implementedEvents();
		$this->assertEquals($expected, $result);
	}

}
