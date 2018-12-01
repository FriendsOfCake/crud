<?php
namespace Crud\TestSuite;

use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class TestCase extends \Cake\TestSuite\TestCase
{

    use AccessibilityHelperTrait;
    use CounterHelperTrait;
    use CrudTestTrait;

    /**
     * [setUp description]
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->resetReflectionCache();
    }

    /**
     * Helper method for check deprecation methods
     *
     * @param callable $callable callable function that will receive asserts
     * @return void
     */
    public function deprecated($callable)
    {
        $errorLevel = error_reporting();
        error_reporting(E_ALL ^ E_USER_DEPRECATED);
        try {
            $callable();
        } finally {
            error_reporting($errorLevel);
        }
    }
}
