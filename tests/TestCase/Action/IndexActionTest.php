<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexActionTest extends IntegrationTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = ['plugin.crud.blogs'];

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
		$this->_eventManager->attach(
			function ($event) {
				$this->_subscribeToEvents($this->_controller);
			},
			'Dispatcher.beforeDispatch',
			['priority' => 1000]
		);

		$this->{$method}('/blogs');
		$this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $this->_response->body());
		$this->assertEvents(['beforePaginate', 'afterPaginate',	'beforeRender']);
		$this->assertNotNull($this->viewVariable('viewVar'));
		$this->assertNotNull($this->viewVariable('blogs'));
		$this->assertNotNull($this->viewVariable('success'));
	}

/**
 * Test that changing the viewVar reflects in controller::$viewVar
 *
 * @return void
 */
	public function testGetWithViewVar() {
		$this->_eventManager->attach(
			function ($event) {
				$this->_controller->Crud->action('index')->viewVar('items');
				$this->_subscribeToEvents($this->_controller);
			},
			'Dispatcher.beforeDispatch',
			['priority' => 1000]
		);

		$this->get('/blogs');

		$this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $this->_response->body());
		$this->assertEvents(['beforePaginate', 'afterPaginate',	'beforeRender']);
		$this->assertNotNull($this->viewVariable('viewVar'));
		$this->assertNotNull($this->viewVariable('items'));
		$this->assertNotNull($this->viewVariable('success'));
	}

}
