<?php
namespace Crud\Test\TestSuite;

use Cake\Core\Plugin;
use Cake\TestSuite\TestSuite;

class AllTest extends TestSuite
{

    public static function suite()
    {
        $suite = new TestSuite('All Crud plugin tests');

        $path = Plugin::path('Crud');
        $testPath = $path . DS . 'tests' . DS . 'TestCase';
        if (!is_dir($testPath)) {
            return $suite;
        }

        $suite->addTestDirectoryRecursive($testPath);

        return $suite;
    }
}
