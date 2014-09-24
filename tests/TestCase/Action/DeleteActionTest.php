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
class DeleteActionTest extends ControllerTestCase {

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
 * Data provider with all HTTP verbs
 *
 * @return array
 */
	public function allHttpMethodProvider() {
		return [
			['get'],
			['post'],
			['put'],
			['delete']
		];
	}

/**
 * Test the normal HTTP flow for all HTTP verbs
 *
 * @dataProvider allHttpMethodProvider
 * @return void
 */
	public function testAllRequestMethods($method) {
		$this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);
		$this->_subscribeToEvents();

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Successfully deleted blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message success', 'original' => 'Successfully deleted blog'],
					'key' => 'flash'
				]
			);

		$this->controller->Blogs = $this->getModel($this->tableClass, ['delete'], 'Blogs', 'blogs');
		$this->controller->Blogs
			->expects($this->once())
			->method('delete')
			->will($this->returnValue(true));

		$result = $this->_testAction('/blogs/delete/1', ['method' => $method]);

		$this->assertEvents(['beforeFind', 'afterFind',	'beforeDelete', 'afterDelete', 'setFlash', 'beforeRedirect']);
		$this->assertTrue($this->_subject->success);
		$this->assertRedirect('/blogs');
	}

/**
 * Test the flow when the beforeDelete event is stopped
 *
 * @return void
 */
	public function testStopDelete() {
		$this->generate($this->controllerClass, [
			'components' => ['Flash' => ['set']]
		]);
		$this->_subscribeToEvents();

		$this->controller->Crud->on('beforeDelete', function($event) {
			$event->stopPropagation();
		});

		$this->controller->Blogs = $this->getModel($this->tableClass, ['delete'], 'Blogs', 'blogs');
		$this->controller->Blogs
			->expects($this->never())
			->method('delete');

		$this->controller->Flash
			->expects($this->once())
			->method('set')
			->with(
				'Could not delete blog',
				[
					'element' => 'default',
					'params' => ['class' => 'message error', 'original' => 'Could not delete blog'],
					'key' => 'flash'
				]
			);

		$result = $this->_testAction('/blogs/delete/1');

		$this->assertEvents(['beforeFind', 'afterFind',	'beforeDelete', 'setFlash', 'beforeRedirect']);
		$this->assertFalse($this->_subject->success);
		$this->assertRedirect('/blogs');
	}

}
