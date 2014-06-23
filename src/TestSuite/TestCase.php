<?php
namespace Crud\Testsuite;

use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class TestCase extends \Cake\TestSuite\TestCase {

	use AccessibilityHelperTrait;
	use CounterHelperTrait;
	use CrudTestTrait;

	public function setUp() {
		parent::setUp();
		$this->resetReflectionCache();
	}

}
