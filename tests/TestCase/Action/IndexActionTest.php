<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\ControllerTestCase;
use Crud\Test\App\Controller\BlogsController;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexActionTest extends ControllerTestCase {

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
	public function testGet($method) {
		$controller = $this->generate($this->controllerClass);
		$this->_subscribeToEvents();

		$result = $this->_testAction('/blogs', compact('method'));
		$this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $result);
		$this->assertEvents(['beforePaginate', 'afterPaginate',	'beforeRender']);
		$this->assertEquals(['viewVar', 'blogs', 'success'], array_keys($this->vars));
	}

/**
 * Test that changing the viewVar reflects in controller::$viewVar
 *
 * @return void
 */
	public function testGetWithViewVar() {
		$controller = $this->generate($this->controllerClass);
		$controller->Crud->action('index')->viewVar('items');
		$this->_subscribeToEvents();

		$result = $this->_testAction('/blogs', compact('method'));
		$this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $result);
		$this->assertEvents(['beforePaginate', 'afterPaginate',	'beforeRender']);
		$this->assertEquals(['viewVar', 'items', 'success'], array_keys($this->vars));
	}

}
