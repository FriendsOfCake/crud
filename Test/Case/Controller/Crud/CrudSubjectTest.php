<?php

App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudSubjectTest extends CakeTestCase {

	public function setup() {
		parent::setup();

		$this->Subject = new CrudSubject(array('action' => 'index'));
	}

	public function teardown() {
		parent::teardown();

		unset($this->Subject);
	}

/**
 * Test that shouldProcess works
 *
 * Our action is "index"
 *
 * @covers CrudSubject::shouldProcess
 * @return void
 */
	public function testShouldProcess() {
		$this->assertTrue($this->Subject->shouldProcess('only', 'index'));
		$this->assertFalse($this->Subject->shouldProcess('only', 'view'));
		$this->assertTrue($this->Subject->shouldProcess('only', array('index')));
		$this->assertFalse($this->Subject->shouldProcess('only', array('view')));

		$this->assertFalse($this->Subject->shouldProcess('not', array('index')));
		$this->assertTrue($this->Subject->shouldProcess('not', array('view')));

		$this->assertFalse($this->Subject->shouldProcess('not', 'index'));
		$this->assertTrue($this->Subject->shouldProcess('not', 'view'));
	}

/**
 * testInvalidMode
 *
 * @covers CrudSubject::shouldProcess
 * @expectedException CakeException
 * @expectedExceptionMessage Invalid mode
 * @return void
 */
	public function testInvalidMode() {
		$this->Subject->shouldProcess('invalid');
	}

}
