<?php
declare(strict_types=1);

namespace Crud\TestSuite;

use Cake\TestSuite\TestCase as CakeTestCase;
use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class TestCase extends CakeTestCase
{
    use AccessibilityHelperTrait;
    use CrudTestTrait;

    /**
     * [setUp description]
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetReflectionCache();
    }
}
