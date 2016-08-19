<?php
namespace Crud\Test\TestSuite;

use Cake\Core\Plugin;
use Cake\TestSuite\TestSuite;

class ActionsTest extends \PHPUnit_Framework_TestSuite
{

    public static function suite()
    {
        $suite = new TestSuite('All CRUD action tests');

        $path = Plugin::path('Crud');
        $testPath = $path . '/tests/TestCase';
        if (!is_dir($testPath)) {
            return $suite;
        }

        $suite->addTestDirectoryRecursive($testPath . '/Action');

        return $suite;
    }
}
