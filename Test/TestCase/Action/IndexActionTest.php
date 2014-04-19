<?php
namespace Crud\Test\TestCase\Action;

use Crud\Test\App\Controller\BlogsController;
use Crud\TestSuite\ControllerTestCase;

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
	public $fixtures = ['plugin.crud.blog'];

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
	public function testGet() {
		$controller = $this->generate($this->controllerClass);
		$result = $this->_testAction('/blogs');

		debug($result);

		$expected = ['tag' => 'legend', 'content' => 'New Blog'];
		$this->assertTag($expected, $result, 'legend do not match the expected value');

		$expected = ['id' => 'id', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"id" do not match the expected value');

		$expected = ['id' => 'name', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"name" do not match the expected value');

		$expected = ['id' => 'body', 'attributes' => ['value' => '']];
		$this->assertTag($expected, $result, '"body" do not match the expected value');
	}

}
