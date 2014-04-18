<?php
namespace Crud\Test\TestCase\Action;

use Crud\Test\App\Controller\BlogsController;
use Crud\TestSuite\ControllerTestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddTest extends ControllerTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = ['plugin.crud.blog'];

	protected $_controllerClass = '\Crud\Test\App\Controller\BlogsController';

/**
 * Test the normal HTTP GET flow of _get
 *
 * @return void
 */
	public function testActionGet() {
		$controller = $this->generate($this->_controllerClass);
		$result = $this->_testAction('/blogs/add');

		$expected = ['tag' => 'legend', 'content' => 'New Blog'];
		$this->assertTag($expected, $result, 'legend do not match the expected value');

		$expected = ['id' => 'id', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"id" do not match the expected value');

		$expected = ['id' => 'name', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"name" do not match the expected value');

		$expected = ['id' => 'body', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"body" do not match the expected value');
	}

/**
 * Test the normal HTTP GET flow of _get with query args
 *
 * Providing ?name=test should fill out the value in the 'name' input field
 *
 * @return void
 */
	public function testActionGetWithQueryArgs() {
		$controller = $this->generate($this->_controllerClass);
		$result = $this->_testAction('/blogs/add?name=test');

		$expected = ['tag' => 'legend', 'content' => 'New Blog'];
		$this->assertTag($expected, $result, 'legend do not match the expected value');

		$expected = ['id' => 'id', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"id" do not match the expected value');

		$expected = ['id' => 'name', 'attributes' => ['value' => 'test']];
		$this->assertTag($expected, $result, '"name" do not match the expected value');

		$expected = ['id' => 'body', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"body" do not match the expected value');
	}

/**
 * Test POST will create a record
 *
 * @return void
 */
	public function testActionPost() {
		$controller = $this->generate($this->_controllerClass, [
			'components' => ['Session' => ['setFlash']]
		]);

		$subject = null;
		$controller->Crud->on('afterSave', function($event) use (&$subject) {
			$subject = $event->subject;
		});

		$controller->Session
			->expects($this->once())
			->method('setFlash')
			->with(
				'Successfully created blog',
				'default',
				[
					'class' => 'message success',
					'original' => 'Successfully created blog'
				],
				'flash'
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => ['name' => 'Hello World', 'body' => 'Pretty hot body']
		]);

		var_dump($subject);
		$this->assertTrue($subject->success);
		$this->assertTrue($subject->created);
		$this->assertEquals('/blogs', $controller->response->location(), 'Was not redirected to index()');
	}

}
