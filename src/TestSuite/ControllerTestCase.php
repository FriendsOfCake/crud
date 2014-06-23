<?php
namespace Crud\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class ControllerTestCase extends \Cake\TestSuite\ControllerTestCase {

	use AccessibilityHelperTrait;
	use CounterHelperTrait;
	use CrudTestTrait;

	public function setUp() {
		parent::setUp();
		$this->resetReflectionCache();

		$existing = Configure::read('App.paths.templates');
		$existing[] = Plugin::path('Crud') . 'tests/App/Template/';
		Configure::write('App.paths.templates', $existing);
	}

}
