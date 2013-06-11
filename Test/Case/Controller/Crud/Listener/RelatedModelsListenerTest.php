<?php

App::uses('CakeEvent', 'Event');
App::uses('RelatedModelsListener', 'Crud.Controller/Crud/Listener');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class RelatedModelListenerTest extends CakeTestCase {

/**
 * There is no unit tests for this class at the moment
 * The class is tested through integration tests in CrudComponent
 *
 * @return void
 */
	public function testEmpty() {
		$this->assertTrue(true);
	}

}
