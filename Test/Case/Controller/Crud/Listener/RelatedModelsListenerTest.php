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
 * Tests that when there is no config for relatedLists then it will be enabled
 *
 * @return void
 */
	public function testEnable() {
		$subject = new CrudSubject;
		$subject->crud = $this->getMock('stdClass', array('action'));
		$action = $this->getMock('stdClass', array('config'));
		$listener = new RelatedModelsListener($subject);

		$subject->crud->expects($this->once())
			->method('action')
			->with('index')
			->will($this->returnValue($action));
		$action->expects($this->at(0))->method('config')->with('relatedLists');
		$action->expects($this->at(1))->method('config')->with('relatedLists', true);
		$listener->enable('index');
	}

}
