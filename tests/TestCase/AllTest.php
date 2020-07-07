<?php
declare(strict_types=1);

namespace Crud\Test\TestSuite;

use Cake\TestSuite\TestSuite;

class AllTest extends TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('All Crud plugin tests');

        $testPath = ROOT . DS . 'tests' . DS . 'TestCase';
        if (!is_dir($testPath)) {
            return $suite;
        }

        $suite->addTestDirectoryRecursive($testPath);

        return $suite;
    }
}
