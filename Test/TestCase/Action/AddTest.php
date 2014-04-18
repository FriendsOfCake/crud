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

	public $controllerClass = '\Crud\Test\App\Controller\BlogsController';

	public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

/**
 * Test the normal HTTP GET flow of _get
 *
 * @return void
 */
	public function testActionGet() {
		$controller = $this->generate($this->controllerClass);
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
		$controller = $this->generate($this->controllerClass);
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
		$this->controller = $this->generate($this->controllerClass, [
			'components' => ['Session' => ['setFlash']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Session
			->expects($this->once())
			->method('setFlash')
			->with(
				'Successfully created blog',
				'default',
				['class' => 'message success', 'original' => 'Successfully created blog'],
				'flash'
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => ['name' => 'Hello World', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeSave', 'afterSave',	'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertTrue($this->_subject->created);
		$this->assertRedirect('/blogs');
	}

/**
 * Test POST with unsuccessful save()
 *
 * @return void
 */
	public function testActionPostErrorSave() {
		$this->generate($this->controllerClass, [
			'components' => ['Session' => ['setFlash']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Blogs = $this->getModel($this->tableClass, ['save'], 'Blogs', 'blogs');

		$this->controller->Blogs
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$this->controller->Session
			->expects($this->once())
			->method('setFlash')
			->with(
				'Could not create blog',
				'default',
				['class' => 'message error', 'original' => 'Could not create blog'],
				'flash'
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => ['name' => 'Hello World', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);
		$this->assertFalse($this->_subject->success);
		$this->assertFalse($this->_subject->created);
	}

/**
 * Test POST with validation errors
 *
 * @return void
 */
	public function testActionPostValidationErrors() {
		$this->generate($this->controllerClass, [
			'components' => ['Session' => ['setFlash']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Blogs = $this->getModel($this->tableClass, null, 'Blogs', 'blogs');
		$this->controller->Blogs
			->validator()
			->validatePresence('name')
			->add('name', [
				'length' => [
					'rule' => ['minLength', 10],
					'message' => 'Name need to be at least 10 characters long',
				]
			]);

		$this->controller->Session
			->expects($this->once())
			->method('setFlash')
			->with(
				'Could not create blog',
				'default',
				['class' => 'message error', 'original' => 'Could not create blog'],
				'flash'
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => ['name' => 'Hello', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

		$this->assertFalse($this->_subject->success);
		$this->assertFalse($this->_subject->created);

		$expected = ['class' => 'error-message', 'content' => 'Name need to be at least 10 characters long'];
		$this->assertTag($expected, $result, 'Could not find validation error in HTML');
	}

}
