<?php
namespace Crud\Test\TestSuite;

use Cake\Core\Plugin;
use Cake\TestSuite\TestSuite;

class ListenersTest extends \PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new TestSuite('All CRUD listener tests');

        $path = Plugin::path('Crud');
        $testPath = $path . DS . 'tests' . DS . 'TestCase';
        if (!is_dir($testPath)) {
            return $suite;
        }

        $suite->addTestDirectoryRecursive($testPath . DS . 'Listener');

        return $suite;
    }
}
