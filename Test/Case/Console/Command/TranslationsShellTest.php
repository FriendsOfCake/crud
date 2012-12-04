<?php

App::uses('TranslationsShell', 'Crud.Console/Command');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');

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
			array('in', 'out', 'hr', 'err', '_stop', '_writeFile'),
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
}
