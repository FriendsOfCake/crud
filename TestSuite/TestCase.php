<?php
namespace Crud\Testsuite;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class TestCase extends \Cake\TestSuite\TestCase {

	use \FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
	use \FriendsOfCake\TestUtilities\CounterHelperTrait;

	public function setUp() {
		parent::setUp();
		$this->resetReflectionCache();
	}

}
