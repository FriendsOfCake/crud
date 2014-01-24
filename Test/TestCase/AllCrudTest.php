<?php
namespace Crud\Test\TestSuite;

use Cake\TestSuite\TestSuite;
use Cake\Core\App;

class AllCrudTest extends \PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new TestSuite('All Crud plugin tests');

		$path = App::pluginPath('Crud');
		$testPath = $path . DS . 'Test' . DS . 'TestCase';
		if (!is_dir($testPath)) {
			return $suite;
		}

		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($testPath), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $folder) {
			$folder = (string)$folder;
			$folderName = basename($folder);

			if ($folderName === '.' || $folderName === '..') {
				continue;
			}
			$suite->addTestDirectory($folder);
		}

		$suite->addTestDirectory($testPath);
		return $suite;
	}

}
