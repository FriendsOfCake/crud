<?php
namespace Crud\Test\TestSuite;

use Cake\TestSuite\TestSuite;
use Cake\Core\App;

class ListenersTest extends \PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new TestSuite('All CRUD listener tests');

		$path = App::pluginPath('Crud');
		$testPath = $path . DS . 'Test' . DS . 'TestCase';
		if (!is_dir($testPath)) {
			return $suite;
		}

		$suite->addTestDirectoryRecursive($testPath . DS . 'Listener');
		return $suite;
	}

}
