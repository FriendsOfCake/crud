<?php
declare(strict_types=1);

namespace Crud\Test\TestSuite;

use Cake\TestSuite\TestSuite;

class ActionsTest extends TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('All CRUD action tests');

        $testPath = ROOT . '/tests/TestCase';
        if (!is_dir($testPath)) {
            return $suite;
        }

        $suite->addTestDirectoryRecursive($testPath . '/Action');

        return $suite;
    }
}
