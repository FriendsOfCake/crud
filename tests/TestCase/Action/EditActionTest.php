<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\ControllerTestCase;
use Crud\Test\App\Controller\BlogsController;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditActionTest extends ControllerTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = ['plugin.crud.blogs'];

/**
 * Controller class to mock on
 *
 * @var string
 */
	public $controllerClass = '\Crud\Test\App\Controller\BlogsController';

/**
 * Table class to mock on
 *
 * @var string
 */
	public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

/**
 * Test the normal HTTP GET flow of _get
 *
 * @return void
 */
	public function testActionGet() {
		$controller = $this->generate($this->controllerClass);
		$result = $this->_testAction('/blogs/edit/1');

		$expected = ['tag' => 'legend', 'content' => 'Edit Blog'];
		$this->assertTag($expected, $result, 'legend do not match the expected value');

		$expected = ['id' => 'id', 'attributes' => ['value' => '1']];
		$this->assertTag($expected, $result, '"id" do not match the expected value');

		$expected = ['id' => 'name', 'attributes' => ['value' => '1st post']];
		$this->assertTag($expected, $result, '"name" do not match the expected value');

		$expected = ['id' => 'body', 'content' => '1st post body'];
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
		$result = $this->_testAction('/blogs/edit/1?name=test');

		$expected = ['tag' => 'legend', 'content' => 'Edit Blog'];
		$this->assertTag($expected, $result, 'legend do not match the expected value');

		$expected = ['id' => 'id', 'attributes' => ['value' => '1']];
		$this->assertTag($expected, $result, '"id" do not match the expected value');

		$expected = ['id' => 'name', 'attributes' => ['value' => '1st post']];
		$this->assertTag($expected, $result, '"name" do not match the expected value');

		$expected = ['id' => 'body', 'content' => '1st post body'];
		$this->assertTag($expected, $result, '"body" do not match the expected value');
	}

/**
 * Test POST will create a record
 *
 * @return void
 */
	public function testActionPost() {
		$this->controller = $this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Successfully updated blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message success', 'original' => 'Successfully updated blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/edit/1', [
			'method' => 'POST',
			'data' => ['name' => 'Hello World', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeFind', 'afterFind',	'beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertFalse($this->_subject->created);
		$this->assertRedirect('/blogs');
	}

/**
 * Test POST with unsuccessful save()
 *
 * @return void
 */
	public function testActionPostErrorSave() {
		$this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Blogs = $this->getModel($this->tableClass, ['save'], 'Blogs', 'blogs');

		$this->controller->Blogs
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Could not update blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message error', 'original' => 'Could not update blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/edit/1', [
			'method' => 'POST',
			'data' => ['name' => 'Hello World', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeFind', 'afterFind',	'beforeSave', 'afterSave', 'setFlash', 'beforeRender']);
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
			'components' => ['Flash' => ['set']]
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

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Could not update blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message error', 'original' => 'Could not update blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/edit/1', [
			'method' => 'POST',
			'data' => ['name' => 'Hello', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeFind', 'afterFind',	'beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

		$this->assertFalse($this->_subject->success);
		$this->assertFalse($this->_subject->created);

		$expected = ['class' => 'error-message', 'content' => 'Name need to be at least 10 characters long'];
		$this->assertTag($expected, $result, 'Could not find validation error in HTML');
	}

}
