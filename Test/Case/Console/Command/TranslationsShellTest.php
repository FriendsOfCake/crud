<?php

App::uses('TranslationsShell', 'Crud.Console/Command');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');

/**
 * TranslationsShellTest
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 */
class TranslationsShellTest extends CakeTestCase {

/**
 * setupBeforeClass
 *
 * Manipulate protected properties/methods to make them directly accessible
 * This permits testing the internal state of the class without creating a test
 * double
 *
 * @return void
 */
	public static function setupBeforeClass() {
		$class = new ReflectionClass('TranslationsShell');

		$property = $class->getProperty('_strings');
		$property->setAccessible(true);
	}

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
			array('in', 'out', 'hr', 'err', '_stop', '_getModels'),
			array($this->out, $this->out, $this->in)
		);

		parent::setUp();
	}

/**
 * testGenerateTranslations
 *
 * @return void
 */
	public function testGenerateTranslations() {
		$method = new ReflectionMethod('TranslationsShell', '_initializeMessages');
		$method->setAccessible(true);
		$method->invoke($this->Shell);

		$method = new ReflectionMethod('TranslationsShell', '_generateTranslations');
		$method->setAccessible(true);
		$method->invoke($this->Shell, false);

		$expected = array(
			"",
			"/**",
			" * Common CRUD Component translations",
			" */",
			"__d('crud', 'Invalid HTTP request');",
			"__d('crud', 'Invalid id');"
		);
		$this->assertSame($expected, $this->Shell->_strings);
	}

/**
 * testGenerateTranslationsForAModel
 *
 * @return void
 */
	public function testGenerateTranslationsForAModel() {
		$method = new ReflectionMethod('TranslationsShell', '_initializeMessages');
		$method->setAccessible(true);
		$method->invoke($this->Shell);

		$method = new ReflectionMethod('TranslationsShell', '_generateTranslations');
		$method->setAccessible(true);
		$method->invoke($this->Shell, 'Example');

		$expected = array(
			"",
			"/**",
			" * Example CRUD Component translations",
			" */",
			"__d('crud', 'Successfully created Example');",
			"__d('crud', 'Could not create Example');",
			"__d('crud', 'Example was successfully updated');",
			"__d('crud', 'Could not update Example');",
			"__d('crud', 'Successfully deleted Example');",
			"__d('crud', 'Could not delete Example');",
			"__d('crud', 'Could not find Example');"
		);
		$this->assertSame($expected, $this->Shell->_strings);
	}

	public function testGenerateFile() {
		$this->Shell
			->expects($this->once())
			->method('_getModels')
			->will($this->returnValue(array('Example')));

		$path = TMP . 'crud_translations_shell_test.php';
		$this->Shell->path($path);
		$this->Shell->generate();

		$this->assertFileExists($path);

		$contents = file_get_contents($path);
		$expected = <<<END
<?php

/**
 * Common CRUD Component translations
 */
__d('crud', 'Invalid HTTP request');
__d('crud', 'Invalid id');

/**
 * Example CRUD Component translations
 */
__d('crud', 'Successfully created Example');
__d('crud', 'Could not create Example');
__d('crud', 'Example was successfully updated');
__d('crud', 'Could not update Example');
__d('crud', 'Successfully deleted Example');
__d('crud', 'Could not delete Example');
__d('crud', 'Could not find Example');
END;

		$this->assertSame(trim($expected), trim($contents));

		unlink($path);
	}
}
