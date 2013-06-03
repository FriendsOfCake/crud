<?php

App::uses('TranslationsEvent', 'Crud.Controller/Listener');
App::uses('CakeEvent', 'Event');

class TranslationsListenerTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		$this->Translations = new TranslationsListener(new CrudSubject());
	}

	public function tearDown() {
		parent::tearDown();

		unset($this->Translations);
	}

/**
 * Test the default config matches the expected
 *
 * @return void
 */
	public function testDefaultConfig() {
		$config = $this->Translations->config();
		$expected = array(
			'domain' => 'crud',
			'name' => null,
			'create' => array(
				'success' => array('message' => 'Successfully created {name}', 'element' => 'success'),
				'error' => array('message' => 'Could not create {name}', 'element' => 'error')
			),
			'update' => array(
				'success' => array('message' => '{name} was successfully updated', 'element' => 'success'),
				'error' => array('message' => 'Could not update {name}', 'element' => 'error')
			),
			'delete' => array(
				'success' => array('message' => 'Successfully deleted {name}', 'element' => 'success'),
				'error' => array('message' => 'Could not delete {name}', 'element' => 'error')
			),
			'find' => array(
				'error' => array('message' => 'Could not find {name}', 'element' => 'error')
			),
			'invalid_http_request' => array(
				'error' => array('message' => 'Invalid HTTP request', 'element' => 'error'),
			),
			'invalid_id' => array(
				'error' => array('message' => 'Invalid id', 'element' => 'error')
			)
		);

		$this->assertEquals($expected, $config);
	}

	public function testCanOverrideConfig() {
		$override = array(
			'create' => array(
				'success' => array('message' => 'Success!', 'element' => 'success'),
				'error' => array('message' => 'Denied!', 'element' => 'error')
			)
		);
		$config = $this->Translations->config($override);

		$expected = array(
			'domain' => 'crud',
			'name' => null,
			'create' => array(
				'success' => array('message' => 'Success!', 'element' => 'success'),
				'error' => array('message' => 'Denied!', 'element' => 'error')
			),
			'update' => array(
				'success' => array('message' => '{name} was successfully updated', 'element' => 'success'),
				'error' => array('message' => 'Could not update {name}', 'element' => 'error')
			),
			'delete' => array(
				'success' => array('message' => 'Successfully deleted {name}', 'element' => 'success'),
				'error' => array('message' => 'Could not delete {name}', 'element' => 'error')
			),
			'find' => array(
				'error' => array('message' => 'Could not find {name}', 'element' => 'error')
			),
			'invalid_http_request' => array(
				'error' => array('message' => 'Invalid HTTP request', 'element' => 'error'),
			),
			'invalid_id' => array(
				'error' => array('message' => 'Invalid id', 'element' => 'error')
			)
		);
		$this->assertEquals($expected, $config);
	}

/**
 * Test updating config with key + string value works
 *
 * @return void
 */
	public function testConfigChangeSingleKey() {
		$expected = 'hello world {name}';
		$this->Translations->config('create.success.message', $expected);
		$this->assertEquals($expected, $this->Translations->config('create.success.message'));
	}

/**
 * Test updating config with key + single array value works
 *
 * @return void
 */
	public function testConfigChangeArraySingleKey() {
		$expected = array('message' => 'hello world {name}');
		$this->Translations->config('create.success', $expected);
		$this->assertEquals($expected + array('element' => 'success'), ($this->Translations->config('create.success') + array('element' => 'success')));
	}

/**
 * Test updating config with key + multiple array values work
 *
 * @return void
 */
	public function testConfigChangeArrayMultiKey() {
		$expected = array('message' => 'hello world {name}', 'element' => 'sample');
		$this->Translations->config('create.success', $expected);
		$this->assertEquals($expected, $this->Translations->config('create.success'));
	}

/**
 * Test setFlash basic configurations
 *
 * @return void
 */
	public function testSetFlashBasic() {
		// Create our "subject"
		$std = new StdClass();
		$std->type = 'create.success';
		$std->name = 'Blog';

		// Create event
		$Event = new CakeEvent('Crud.afterSave', $std);

		// "trigger" our callback
		$this->Translations->setFlash($Event);

		// Compare
		$this->assertSame('Successfully created Blog', $std->message);
		$this->assertSame('success', $std->element);
		$this->assertSame(array(), $std->params);
		$this->assertSame('flash', $std->key);

		// Update configuration for create.success key
		$this->Translations->config('create.success', array('key' => 'new_flash', 'params' => array('id' => 1)));

		// Create new event
		$Event = new CakeEvent('Crud.afterSave', $std);

		// "trigger" our callback
		$this->Translations->setFlash($Event);

		// Check if our changed configurations gave the expected
		$this->assertSame('Successfully created Blog', $std->message);
		$this->assertSame('success', $std->element);
		$this->assertSame(array('id' => 1), $std->params);
		$this->assertSame('new_flash', $std->key);
	}

/**
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Invalid flash type
 * @return void
 */
	public function testSetFlashInvalidTypeThrowException() {
		$std = new StdClass();
		$std->type = 'create.';
		$std->name = 'Blog';

		$Event = new CakeEvent('Crud.afterSave', $std);
		$this->Translations->setFlash($Event);
	}

/**
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Missing flash type
 * @return void
 */
	public function testSetFlashMissingTypeThrowException() {
		$std = new StdClass();
		$std->name = 'Blog';

		$Event = new CakeEvent('Crud.afterSave', $std);
		$this->Translations->setFlash($Event);
	}
}
