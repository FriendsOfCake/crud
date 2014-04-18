<?php
namespace Crud\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Plugin;
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

	public function setUp() {
		parent::setUp();
		$this->resetReflectionCache();

		$existing = Configure::read('App.paths.templates');
		$existing[] = Plugin::path('Crud') . 'Test/App/Template/';
		Configure::write('App.paths.templates', $existing);
	}

}
