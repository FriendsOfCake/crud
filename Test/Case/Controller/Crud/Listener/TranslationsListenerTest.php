<?php

App::uses('CakeEvent', 'Event');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('TranslationsListener', 'Crud.Controller/Crud/Listener');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class TranslationsListenerTest extends ControllerTestCase {

/**
 * fixtures
 *
 * Use the core posts fixture to have something to work on.
 * What fixture is used is almost irrelevant, was chosen as it is simple
 */
	public $fixtures = array('core.post', 'core.author', 'core.tag', 'plugin.crud.posts_tag');

	public function setUp() {
		parent::setUp();
		$this->Translations = new TranslationsListener(new CrudSubject(array('crud' => new StdClass)));
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
				'success' => array('message' => 'Successfully created {name}'),
				'error' => array('message' => 'Could not create {name}')
			),
			'update' => array(
				'success' => array('message' => '{name} was successfully updated'),
				'error' => array('message' => 'Could not update {name}')
			),
			'delete' => array(
				'success' => array('message' => 'Successfully deleted {name}'),
				'error' => array('message' => 'Could not delete {name}')
			),
			'find' => array(
				'error' => array('message' => 'Could not find {name}')
			),
			'invalid_http_request' => array(
				'error' => array('message' => 'Invalid HTTP request'),
			),
			'invalid_id' => array(
				'error' => array('message' => 'Invalid id')
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

		$this->Translations->config($override);
		$config = $this->Translations->config();

		$expected = array(
			'domain' => 'crud',
			'name' => null,
			'create' => array(
				'success' => array('message' => 'Success!', 'element' => 'success'),
				'error' => array('message' => 'Denied!', 'element' => 'error')
			),
			'update' => array(
				'success' => array('message' => '{name} was successfully updated'),
				'error' => array('message' => 'Could not update {name}')
			),
			'delete' => array(
				'success' => array('message' => 'Successfully deleted {name}'),
				'error' => array('message' => 'Could not delete {name}')
			),
			'find' => array(
				'error' => array('message' => 'Could not find {name}')
			),
			'invalid_http_request' => array(
				'error' => array('message' => 'Invalid HTTP request'),
			),
			'invalid_id' => array(
				'error' => array('message' => 'Invalid id')
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
		$this->assertEquals($expected + array('element' => 'default'), ($this->Translations->config('create.success') + array('element' => 'default')));
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
		$this->assertSame('default', $std->element);
		$this->assertSame(array('class' => 'message success'), $std->params);
		$this->assertSame('flash', $std->key);

		// Update configuration for create.success key
		$this->Translations->config('create.success', array(
			'key' => 'new_flash',
			'params' => array('id' => 1, 'class' => 'msg')
		));

		// Create new event
		$Event = new CakeEvent('Crud.afterSave', $std);

		// "trigger" our callback
		$this->Translations->setFlash($Event);

		// Check if our changed configurations gave the expected
		$this->assertSame('Successfully created Blog', $std->message);
		$this->assertSame('default', $std->element);
		$this->assertSame(array('class' => 'msg success', 'id' => 1), $std->params);
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

/**
 * testAddActionTranslatedBaseline
 *
 * @return void
 */
	public function testAddActionTranslatedBaseline() {
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);

		$this->Controller->Session
			->expects($this->once())
			->method('setFlash')
			->with('Successfully created CrudExample');

		$this->testAction('/add', array(
			'data' => array(
				'CrudExample' => array(
					'title' => __METHOD__,
					'description' => __METHOD__,
					'author_id' => 0
				)
			)
		));
	}

/**
 * testAddActionTranslatedChangedName
 *
 * @return void
 */
	public function testAddActionTranslatedChangedName() {
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);

		$this->Controller->Crud->defaults('listener', 'translations', array('name' => 'Thingy'));
		$this->Controller->Session
			->expects($this->once())
			->method('setFlash')
			->with('Successfully created Thingy');

		$this->testAction('/add', array(
			'data' => array(
				'CrudExample' => array(
					'title' => __METHOD__,
					'description' => __METHOD__,
					'author_id' => 0
				)
			)
		));
	}

/**
 * testAddActionTranslatedChangedName
 *
 * @return void
 */
	public function testAddActionTranslatedChangedMessage() {
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);

		$this->Controller->Crud->defaults('listener', 'translations', array(
			'create' => array('success' => array('message' => "Yay!"))
		));

		$this->Controller->Session
			->expects($this->once())
			->method('setFlash')
			->with('Yay!');

		$this->testAction('/add', array(
			'data' => array(
				'CrudExample' => array(
					'title' => __METHOD__,
					'description' => __METHOD__,
					'author_id' => 0
				)
			)
		));
	}

}
