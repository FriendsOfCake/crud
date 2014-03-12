<?php
namespace Crud\Test\TestSuite;

use Cake\TestSuite\TestSuite;
use Cake\Core\App;

class ActionsTest extends \PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new TestSuite('All CRUD action tests');

		$path = App::pluginPath('Crud');
		$testPath = $path . '/Test/TestCase';
		if (!is_dir($testPath)) {
			return $suite;
		}

		$suite->addTestDirectoryRecursive($testPath . '/Action');
		return $suite;
	}

}
