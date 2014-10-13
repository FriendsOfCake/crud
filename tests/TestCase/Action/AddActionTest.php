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
class AddActionTest extends ControllerTestCase {

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
			'components' => ['Flash' => ['set']]
		]);

		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Successfully created blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
					'key' => 'flash'
				]
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
 * Test POST will create a record and redirect to /blogs/add again
 * if _POST['_add'] is present
 *
 * @return void
 */
	public function testActionPostWithAddRedirect() {
		$controller = $this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);
		$this->_subscribeToEvents();

		$controller->Crud->addListener('Redirect', 'Crud.Redirect');
		$controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Successfully created blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => [
				'name' => 'Hello World',
				'body' => 'Pretty hot body',
				'_add' => 1
			]
		]);

		$this->assertEvents(['beforeSave', 'afterSave',	'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertTrue($this->_subject->created);
		$this->assertRedirect('/blogs/add');
	}

/**
 * Test POST will create a record and redirect to /blogs/edit/$id
 * if _POST['_edit'] is present
 *
 * @return void
 */
	public function testActionPostWithEditRedirect() {
		$controller = $this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);
		$this->_subscribeToEvents();

		$controller->Crud->addListener('Redirect', 'Crud.Redirect');
		$controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Successfully created blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => [
				'name' => 'Hello World',
				'body' => 'Pretty hot body',
				'_edit' => 1
			]
		]);

		$this->assertEvents(['beforeSave', 'afterSave',	'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertTrue($this->_subject->created);
		$this->assertRedirect('/blogs/edit/6');
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

		$this->controller->Blogs = $this->getModel(
			$this->tableClass,
			['save'],
			'Blogs',
			'blogs'
		);

		$this->controller->Blogs
			->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Could not create blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
					'key' => 'flash'
				]
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
				'Could not create blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/add', [
			'method' => 'POST',
			'data' => ['name' => 'Hello', 'body' => 'Pretty hot body']
		]);

		$this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

		$this->assertFalse($this->_subject->success);
		$this->assertFalse($this->_subject->created);

		$expected = [
			'class' => 'error-message',
			'content' => 'Name need to be at least 10 characters long'
		];
		$this->assertTag($expected, $result, 'Could not find validation error in HTML');
	}

/**
 * Data provider with GET and DELETE verbs
 *
 * @return array
 */
	public function apiGetHttpMethodProvider() {
		return [
			['get'],
			['delete']
		];
	}

/**
 * Test HTTP & DELETE verbs using API Listener
 *
 * @dataProvider apiGetHttpMethodProvider
 * @param  string $method
 * @return void
 */
	public function testApiGet($method) {
		$controller = $this->generate($this->controllerClass);
		$controller->Crud->addListener('api', 'Crud.Api');
		$this->setExpectedException(
			'Cake\Network\Exception\BadRequestException',
			'Wrong request method'
		);
		Router::extensions(['json']);
		Router::scope('/', function($routes) {
			$routes->extensions(['json']);
			$routes->fallbacks();
		});

		$this->_testAction('/blogs/add.json', ['method' => $method]);
	}

/**
 * Data provider with PUT and POST verbs
 *
 * @return array
 */
	public function apiUpdateHttpMethodProvider() {
		return [
			['put'],
			['post']
		];
	}

/**
 * Test POST & PUT verbs using API Listener
 *
 * @dataProvider apiUpdateHttpMethodProvider
 * @param  string $method
 * @return void
 */
	public function testApiCreate($method) {
		$controller = $this->generate($this->controllerClass,
			['components' => ['Flash' => ['set']]
		]);

		Router::extensions('json');
		$controller->Crud->addListener('api', 'Crud.Api');
		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->never())
			->method('set');
		$this->controller->RequestHandler->ext = 'json';

		$data = [
			'name' => '6th blog post',
			'body' => 'Amazing blog post'
		];

		$body = $this->_testAction('/blogs/add.json', compact('method', 'data'));
		$this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertTrue($this->_subject->created);
		$this->assertEquals(
			['success' => true, 'data' => ['id' => 6]],
			json_decode($body, true)
		);
	}

/**
 * Test POST & PUT verbs using API Listener
 * with data validation error
 *
 * @dataProvider apiUpdateHttpMethodProvider
 * @param  string $method
 * @return void
 */
	public function testApiCreateError($method) {
		$controller = $this->generate($this->controllerClass,
			['components' => ['Flash' => ['set']]
		]);
		$controller->Blogs = $this->getModel($this->tableClass, null, 'Blogs', 'blogs');
		$controller->Blogs
			->validator()
			->validatePresence('name')
			->add('name', [
				'length' => [
					'rule' => ['minLength', 10],
					'message' => 'Name need to be at least 10 characters long',
				]
			]);
		Router::extensions('json');
		$controller->Crud->addListener('api', 'Crud.Api');
		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->never())
			->method('set');

		$data = [
			'name' => 'too short',
			'body' => 'Amazing blog post'
		];

		$this->setExpectedException(
			'Crud\Error\Exception\ValidationException',
			'A validation error occurred'
		);

		$this->_testAction('/blogs/add.json', compact('method', 'data'));
	}

/**
 * Test POST & PUT verbs using API Listener
 * with data validation errors
 *
 * @dataProvider apiUpdateHttpMethodProvider
 * @param  string $method
 * @return void
 */
	public function testApiCreateErrors($method) {
		$controller = $this->generate($this->controllerClass,
			['components' => ['Flash' => ['set']]
		]);
		$controller->Blogs = $this->getModel($this->tableClass, null, 'Blogs', 'blogs');
		$controller->Blogs
			->validator()
			->validatePresence('name')
			->validatePresence('body')
			->add('name', [
				'length' => [
					'rule' => ['minLength', 10],
					'message' => 'Name need to be at least 10 characters long',
				]
			]);
		Router::extensions('json');
		$controller->Crud->addListener('api', 'Crud.Api');
		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->never())
			->method('set');

		$data = [
			'name' => 'too short'
		];

		$this->setExpectedException(
			'Crud\Error\Exception\ValidationException',
			'2 validation errors occurred'
		);
		$this->_testAction('/blogs/add.json', compact('method', 'data'));
	}

}
