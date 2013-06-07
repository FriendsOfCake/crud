<?php

App::uses('CakeEvent', 'Event');
App::uses('ApiListener', 'Crud.Controller/Crud/Listener');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ApiListenerTest extends CakeTestCase {

	public function testSkip() {
		$this->skipIf(true);
	}

}
