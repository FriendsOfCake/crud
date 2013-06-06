<?php

App::uses('CakeEvent', 'Event');
App::uses('RelatedModelListener', 'Crud.Controller/Crud/Listener');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class RelatedModelListenerTest extends CakeTestCase {

	public function setUp() {
		$this->skipIf(true);
		parent::setUp();
		$this->Translations = new TranslationsListener(new CrudSubject(array('crud' => new StdClass)));
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Translations);
	}

/**
 * Tests that by default Crud component will fetch related associations on add and edit actions
 *
 * @return void
 */
	public function testFetchRelatedDefaults() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');
		$expectedAuthors = array(1 => '1', 2 => '2', 3 => '3', 4 => '4');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->controller->viewVars = array();

		$this->Crud->executeAction('edit', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->controller->viewVars = array();

		$this->Crud->executeAction('admin_add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->controller->viewVars = array();

		$this->Crud->executeAction('admin_edit', array(1));
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertEquals($expectedAuthors, $vars['authors']);
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 *
 * @return void
 */
	public function testFetchRelatedMapped() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);
		$this->Crud->getAction('add')->config('relatedLists', array('Author'));
		$this->Crud->getAction('admin_add')->config('relatedLists', array('Author'));

		$expectedAuthors = array(1 => '1', 2 => '2', 3 => '3', 4 => '4');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->assertFalse(isset($vars['tags']));
		$this->controller->viewVars = array();

		$this->Crud->executeAction('admin_add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->assertFalse(isset($vars['tags']));
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 * using mapRelatedList
 *
 * @return void
 */
	public function testFetchRelatedMappedMethod() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->getListener('related')->map(array('Tag'), 'add');
		$this->Crud->getListener('related')->map(array('Tag'), 'admin_add');
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
		$this->controller->viewVars = array();

		$this->Crud->executeAction('admin_add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 * using mapRelatedList with an 'all' default
 *
 * @return void
 */
	public function testFetchRelatedMappedAll() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->getListener('related')->map(array('Tag'), 'edit');
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->Crud->executeAction('edit', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests that all default for mapped lists will not apply to not enabled actions
 *
 * @return void
 */
	public function testFetchRelatedMappedAllNotEnabled() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->getListener('related')->map(array('Tag'));

		$this->Crud->executeAction('delete', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertFalse(isset($vars['tags']));
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests that mammpe actions are not used if you define specific related models
 * for the mapped controller action
 *
 * @return void
 */
	public function testFetchRelatedSpecificActionMapped() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->getListener('related')->map(array('Tag'), 'admin_add');
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->Crud->executeAction('admin_add', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests beforeListRelated and afterListRelated events
 *
 * @return void
 */
	public function testFetchRelatedEvents() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->getListener('related')->map(array('Tag'), 'add');
		$expectedTags = array(1 => '1', 2 => '2', 'foo' => 'bar');
		$self = $this;

		$this->controller->getEventManager()->attach(function($event) use($self) {
			$self->assertEquals(200, $event->subject->query['limit']);
			$event->subject->query['limit'] = 2;
		}, 'Crud.beforeListRelated');

		$this->controller->getEventManager()->attach(function($event) use($self) {
			$self->assertEquals('tags', $event->subject->viewVar);
			$event->subject->viewVar = 'labels';

			$event->subject->items += array('foo' => 'bar');
		}, 'Crud.afterListRelated');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['labels']);
	}

/**
 * Test mapRelatedList with default config to 'false' for the add action
 *
 * @return void
 */
	public function testRelatedModelsDefaultFalseAdd() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->getListener('related')->map(false, 'add');
		$this->assertEquals(array(), $this->Crud->getListener('related')->models('add'));

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertTrue(empty($vars['tags']));
		$this->assertTrue(empty($vars['authors']));
	}

/**
 * Test mapRelatedList with default config to 'false' for the edit action
 *
 * @return void
 */
	public function testRelatedModelsDefaultFalseEdit() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->getListener('related')->map(false, 'edit');
		$this->assertEquals(array(), $this->Crud->getListener('related')->models('edit'));

		$this->Crud->executeAction('edit');
		$vars = $this->controller->viewVars;
		$this->assertTrue(empty($vars['tags']));
		$this->assertTrue(empty($vars['authors']));
	}

/**
 * Test mapRelatedList with default config to 'true' for the add action
 *
 * @return void
 */
	public function testRelatedModelsDefaultTrueAdd() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->getListener('related')->map(true, 'add');
		$this->assertEquals(array('Author', 'Tag'), $this->Crud->getListener('related')->models('add'));

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('tags' => array(1 => '1', 2 => '2', '3' => '3'), 'authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test mapRelatedList with default config to 'true' for the edit action
 *
 * @return void
 */
	public function testRelatedModelsDefaultTrueEdit() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);

		$this->Crud->getListener('related')->map(true, 'edit');
		$this->assertEquals(array('Author', 'Tag'), $this->Crud->getListener('related')->models('edit'));

		$this->Crud->executeAction('edit', array(3));

		$vars = $this->controller->viewVars;
		$expectedVars = array('tags' => array(1 => '1', 2 => '2', '3' => '3'), 'authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test mapRelatedList with an action mapped using mapAction
 *
 * @return void
 */
	public function testRelatedModelsWithAliasMappedLookup() {
		$this->Crud->getAction('edit')->config('validateId', 'integer');
		$this->model->bindModel(array('belongsTo' => array('Author')), false);

		$this->Crud->mapAction('modify_action', 'edit');
		$this->Crud->getListener('related')->map(true, 'modify_action');
		$this->assertEquals(array('Author'), $this->Crud->getListener('related')->models('modify_action'));

		$this->Crud->executeAction('modify_action', array(1));

		$vars = $this->controller->viewVars;
		$expectedVars = array('authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test if enableRelatedList works with a normal Crud action
 *
 * @return void
 */
	public function testEnableRelatedListStringForIndexAction() {
		$this->Crud->getListener('related')->map('Tag');
		$this->Crud->getListener('related')->enable('index');

		$expected = array('Tag');
		$value = $this->Crud->getListener('related')->models('index');
		$this->assertSame($expected, $value);

		$this->Crud->executeAction('index');

		$this->assertTrue(!empty($this->controller->viewVars['tags']));
		$this->assertTrue(!empty($this->controller->viewVars['items']));

		$this->assertSame(3, sizeof($this->controller->viewVars['tags']));
		$this->assertSame(3, sizeof($this->controller->viewVars['items']));
	}

/**
 * Test if enableRelatedList works with a normal Crud action
 *
 * @return void
 */
	public function testEnableRelatedListArrayForIndexAction() {
		$this->Crud->getListener('related')->map(array('Tag', 'Author'));
		$this->Crud->getListener('related')->enable(array('index'));

		$this->assertSame(array('Tag', 'Author'), $this->Crud->getListener('related')->models('index'));

		$this->Crud->executeAction('index');

		$this->assertTrue(!empty($this->controller->viewVars['tags']));
		$this->assertTrue(!empty($this->controller->viewVars['items']));
		$this->assertTrue(!empty($this->controller->viewVars['authors']));

		$this->assertSame(3, sizeof($this->controller->viewVars['tags']));
		$this->assertSame(3, sizeof($this->controller->viewVars['items']));
		$this->assertSame(4, sizeof($this->controller->viewVars['authors']));
	}
}
