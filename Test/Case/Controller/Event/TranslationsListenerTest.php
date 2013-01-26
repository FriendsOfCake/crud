<?php

App::uses('CakeEvent', 'Event');
App::uses('TranslationsListener', 'Crud.Controller/Event');
App::uses('CrudSubject', 'Crud.Controller/Event');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('ComponentCollection', 'Controller');

class TranslationsListenerTest extends CakeTestCase {

	public function setUp() {
		$subject = new CrudSubject();
		$subject->crud = new CrudComponent(new ComponentCollection());
		$this->TranslationsListener = new TranslationsListener($subject);

		parent::setUp();
	}

	public function tearDown() {
		unset($this->TranslationsListener);
		parent::tearDown();
	}

/**
 * Test the default config matches the expected
 *
 * @return void
 */
	public function testDefaultConfig() {
		$config = $this->TranslationsListener->config();
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
			'error' => array(
				'invalid_http_request' => array('message' => 'Invalid HTTP request', 'element' => 'error'),
				'invalid_id' => array('message' => 'Invalid id', 'element' => 'error')
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
		$config = $this->TranslationsListener->config($override);

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
			'error' => array(
				'invalid_http_request' => array('message' => 'Invalid HTTP request', 'element' => 'error'),
				'invalid_id' => array('message' => 'Invalid id', 'element' => 'error')
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
		$this->TranslationsListener->config('create.success.message', $expected);
		$this->assertEquals($expected, $this->TranslationsListener->config('create.success.message'));
	}

/**
 * Test updating config with key + single array value works
 *
 * @return void
 */
	public function testConfigChangeArraySingleKey() {
		$expected = array('message' => 'hello world {name}');
		$this->TranslationsListener->config('create.success', $expected);
		$this->assertEquals($expected + array('element' => 'success'), ($this->TranslationsListener->config('create.success') + array('element' => 'success')));
	}

/**
 * Test updating config with key + multiple array values work
 *
 * @return void
 */
	public function testConfigChangeArrayMultiKey() {
		$expected = array('message' => 'hello world {name}', 'element' => 'sample');
		$this->TranslationsListener->config('create.success', $expected);
		$this->assertEquals($expected, $this->TranslationsListener->config('create.success'));
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
		$this->TranslationsListener->setFlash($Event);

		// Compare
		$this->assertSame('Successfully created Blog', $std->message);
		$this->assertSame('success', $std->element);
		$this->assertSame(array(), $std->params);
		$this->assertSame('flash', $std->key);

		// Update configuration for create.success key
		$this->TranslationsListener->config('create.success', array('key' => 'new_flash', 'params' => array('id' => 1)));

		// Create new event
		$Event = new CakeEvent('Crud.afterSave', $std);

		// "trigger" our callback
		$this->TranslationsListener->setFlash($Event);

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
		$this->TranslationsListener->setFlash($Event);
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
		$this->TranslationsListener->setFlash($Event);
	}
}
