<?php

App::uses('TranslationsShell', 'Crud.Console/Command');
App::uses('CakeRequest', 'Network');
App::uses('ConsoleInput', 'Console');
App::uses('ConsoleOutput', 'Console');
App::uses('Controller', 'Controller');

/**
 * TranslationsShellTest
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class TranslationsShellTest extends CakeTestCase {

/**
 * setup test
 *
 * @return void
 */
	public function setUp() {
		$this->out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$this->in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Shell = $this->getMock(
			'TranslationsShell',
			array('in', 'out', 'hr', 'err', '_stop', '_getControllers', '_loadController'),
			array($this->out, $this->out, $this->in)
		);

		parent::setUp();
	}

/**
 * testGenerateTranslations
 *
 * With no controllers, nothing's going to happen
 *
 * @return void
 */
	public function testGenerateTranslations() {
		$method = new ReflectionMethod('TranslationsShell', '_processController');
		$method->setAccessible(true);
		$method->invoke($this->Shell, false);

		$expected = array();
		$this->assertSame($expected, $this->Shell->lines);
	}

/**
 * testGenerateTranslationsForAModel
 *
 * @return void
 */
	public function testGenerateTranslationsForAModel() {
		$controller = new Controller(new CakeRequest());
		$controller->Example = new StdClass(); // dummy
		$controller->modelClass = 'Example';
		$controller->components = array(
			'Crud.Crud' => array(
				'actions' => array(
					'index', 'add', 'edit', 'view', 'delete'
				)
			)
		);
		$controller->constructClasses();
		$controller->startupProcess();

		$this->Shell
			->expects($this->once())
			->method('_loadController')
			->will($this->returnValue($controller));

		$method = new ReflectionMethod('TranslationsShell', '_processController');
		$method->setAccessible(true);
		$method->invoke($this->Shell, 'Example');

		$expected = array(
			"",
			"/**",
			" * Example CRUD Component translations",
			" */",
			"__d('crud', 'Successfully created example');",
			"__d('crud', 'Could not create example');",
			"__d('crud', 'Successfully updated example');",
			"__d('crud', 'Could not update example');",
			"__d('crud', 'Successfully deleted example');",
			"__d('crud', 'Could not delete example');"
		);
		$this->assertSame($expected, $this->Shell->lines);
	}

	public function testGenerateFile() {
		$controller = new Controller(new CakeRequest());
		$controller->Example = new StdClass(); // dummy
		$controller->modelClass = 'Example';
		$controller->components = array(
			'Crud.Crud' => array(
				'actions' => array(
					'index', 'add', 'edit', 'view', 'delete'
				)
			)
		);
		$controller->constructClasses();
		$controller->startupProcess();

		$this->Shell
			->expects($this->once())
			->method('_loadController')
			->will($this->returnValue($controller));

		$this->Shell
			->expects($this->once())
			->method('_getControllers')
			->will($this->returnValue(array('Example')));

		$path = TMP . 'crud_translations_shell_test.php';
		if (file_exists($path)) {
			unlink($path);
		}
		$this->Shell->path($path);
		$this->Shell->generate();

		$this->assertFileExists($path);

		$contents = file_get_contents($path);
		$expected = <<<END
<?php

/**
 * Example CRUD Component translations
 */
__d('crud', 'Successfully created example');
__d('crud', 'Could not create example');
__d('crud', 'Successfully updated example');
__d('crud', 'Could not update example');
__d('crud', 'Successfully deleted example');
__d('crud', 'Could not delete example');
END;

		$this->assertSame(trim($expected), trim($contents));

		unlink($path);
	}
}
