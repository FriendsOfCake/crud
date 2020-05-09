<?php
declare(strict_types=1);

namespace Crud\TestSuite;

use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
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
    public function setUp(): void
    {
        parent::setUp();

        $this->resetReflectionCache();
    }
}
