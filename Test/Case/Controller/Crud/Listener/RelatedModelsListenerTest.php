<?php

App::uses('CakeEvent', 'Event');
App::uses('CrudAction', 'Crud.Controller/Crud');
App::uses('RelatedModelsListener', 'Crud.Controller/Crud/Listener');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class RelatedModelListenerTest extends CakeTestCase {

/**
 * testModels
 *
 * @covers RelatedModelsListener::models
 * @return void
 */
	public function testModels() {
		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_handle'))
			->getMock();
		$Action->config('relatedModels', array('Post', 'User'));

		$Listener = $this
			->getMockBuilder('RelatedModelsListener')
			->disableOriginalConstructor()
			->setMethods(array('_action'))
			->getMock();

		$Listener
			->expects($this->once())
			->method('_action')
			->with(NULL)
			->will($this->returnValue($Action));

		$result = $Listener->models();
		$expected = array('Post', 'User');
		$this->assertEqual($result, $expected);
	}

/**
 * testModelsEmpty
 *
 * Test behavior when 'relatedModels' is empty
 *
 * @covers RelatedModelsListener::models
 * @return void
 */
	public function testModelsEmpty() {
		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_handle'))
			->getMock();
		$Action->config('relatedModels', null);

		$Listener = $this
			->getMockBuilder('RelatedModelsListener')
			->disableOriginalConstructor()
			->setMethods(array('_action'))
			->getMock();

		$Listener
			->expects($this->once())
			->method('_action')
			->with(NULL)
			->will($this->returnValue($Action));

		$result = $Listener->models();
		$expected = array();
		$this->assertEqual($result, $expected);
	}

/**
 * testModelsEmpty
 *
 * Test behavior when 'relatedModels' is a string
 *
 * @covers RelatedModelsListener::models
 * @return void
 */
	public function testModelsString() {
		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_handle'))
			->getMock();
		$Action->config('relatedModels', 'Post');

		$Listener = $this
			->getMockBuilder('RelatedModelsListener')
			->disableOriginalConstructor()
			->setMethods(array('_action'))
			->getMock();

		$Listener
			->expects($this->once())
			->method('_action')
			->with(NULL)
			->will($this->returnValue($Action));

		$result = $Listener->models();
		$expected = array('Post');
		$this->assertEqual($result, $expected);
	}

/**
 * testModelsTrue
 *
 * Test behavior when 'relatedModels' is true
 *
 * @covers RelatedModelsListener::models
 * @return void
 */
	public function testModelsTrue() {
		$Model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->setMethods(array('getAssociated'))
			->getMock();

		$Action = $this
			->getMockBuilder('CrudAction')
			->disableOriginalConstructor()
			->setMethods(array('_handle'))
			->getMock();
		$Action->config('relatedModels', true);

		$Listener = $this
			->getMockBuilder('RelatedModelsListener')
			->disableOriginalConstructor()
			->setMethods(array('_action', '_model'))
			->getMock();

		$Listener
			->expects($this->once())
			->method('_action')
			->with(NULL)
			->will($this->returnValue($Action));
		$Listener
			->expects($this->once())
			->method('_model')
			->with()
			->will($this->returnValue($Model));
		$Model
			->expects($this->at(0))
			->method('getAssociated')
			->with('belongsTo')
			->will($this->returnValue(array('Post')));
		$Model
			->expects($this->at(1))
			->method('getAssociated')
			->with('hasAndBelongsToMany')
			->will($this->returnValue(array('Tag')));

		$result = $Listener->models();
		$expected = array('Post', 'Tag');
		$this->assertEqual($result, $expected);
	}

// ---

/**
 * Tests that by default Crud component will fetch related associations on add and edit actions
 *
 * @return void
 */
	public function _testFetchRelatedDefaults() {
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
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 *
 * @return void
 */
	public function _testFetchRelatedMapped() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);
		$this->Crud->action('add')->config('relatedModels', array('Author'));

		$expectedAuthors = array(1 => '1', 2 => '2', 3 => '3', 4 => '4');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->assertFalse(isset($vars['tags']));
		$this->controller->viewVars = array();
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 * using relatedModels
 *
 * @return void
 */
	public function _testFetchRelatedMappedMethod() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->action('add')->config('relatedModels', array('Tag'));
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
		$this->controller->viewVars = array();
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 * using relatedModels with an 'all' default
 *
 * @return void
 */
	public function _testFetchRelatedMappedAll() {
		$this->model->bindModel(array('belongsTo' => array('Author')));
		$this->Crud->action('edit')->config('relatedModels', array('Tag'));
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->controller->Tag = ClassRegistry::init('Tag');
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
	public function _testFetchRelatedMappedAllNotEnabled() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->action('delete')->config('relatedModels', array('Tag'));

		try {
			$this->Crud->executeAction('delete', array('1'));
		} catch (Exception $e) {
			$class = get_class($e);
			$this->assertTrue($e instanceof MethodNotAllowedException, "Exception of class $class, is not a MethodNotAllowedException");
		}

		$vars = $this->controller->viewVars;
		$this->assertFalse(isset($vars['tags']));
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests that relatedModels will not overwrite existing variables
 *
 * @return void
 */
	public function _testFetchRelatedNoOverwrite() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->action('edit')->config('relatedModels', array('Tag'));
		$expectedTags = array('mine', 'tag');

		$this->controller->viewVars['tags'] = $expectedTags;
		$this->Crud->executeAction('edit', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
	}

/**
 * Tests beforeRelatedModel and afterRelatedModel events
 *
 * @return void
 */
	public function _testFetchRelatedEvents() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));
		$this->Crud->action('add')->config('relatedModels', array('Tag'));
		$expectedTags = array(1 => '1', 2 => '2', 'foo' => 'bar');
		$self = $this;

		$this->controller->getEventManager()->attach(function($event) use($self) {
			$event->subject->query['limit'] = 2;
		}, 'Crud.beforeRelatedModel');

		$this->controller->getEventManager()->attach(function($event) use($self) {
			$self->assertEquals('tags', $event->subject->viewVar);
			$event->subject->viewVar = 'labels';

			$event->subject->items += array('foo' => 'bar');
		}, 'Crud.afterRelatedModel');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['labels']);
	}

/**
 * Test relatedModels with default config to 'false' for the add action
 *
 * @return void
 */
	public function _testRelatedModelsDefaultFalseAdd() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->action('add')->config('relatedModels', false);
		$this->assertEquals(array(), $this->Crud->listener('RelatedModels')->models('add'));

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertTrue(empty($vars['tags']));
		$this->assertTrue(empty($vars['authors']));
	}

/**
 * Test relatedModels with default config to 'false' for the edit action
 *
 * @return void
 */
	public function _testRelatedModelsDefaultFalseEdit() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->action('edit')->config('relatedModels', false);
		$this->assertEquals(array(), $this->Crud->listener('RelatedModels')->models('edit'));

		$this->Crud->executeAction('edit', array(1));
		$vars = $this->controller->viewVars;
		$this->assertTrue(empty($vars['tags']));
		$this->assertTrue(empty($vars['authors']));
	}

/**
 * Test relatedModels with default config to 'true' for the add action
 *
 * @return void
 */
	public function _testRelatedModelsDefaultTrueAdd() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')));

		$this->Crud->action('add')->config('relatedModels', true);
		$this->assertEquals(array('Author', 'Tag'), $this->Crud->listener('RelatedModels')->models('add'));

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('tags' => array(1 => '1', 2 => '2', '3' => '3'), 'authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test relatedModels with default config to 'true' for the edit action
 *
 * @return void
 */
	public function _testRelatedModelsDefaultTrueEdit() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);

		$this->Crud->action('edit')->config('relatedModels', true);
		$this->assertEquals(array('Author', 'Tag'), $this->Crud->listener('RelatedModels')->models('edit'));

		$this->Crud->executeAction('edit', array(3));

		$vars = $this->controller->viewVars;
		$expectedVars = array('tags' => array(1 => '1', 2 => '2', '3' => '3'), 'authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test relatedModels with association condtions
 *
 * @return void
 */
	public function _testRelatedModelsConditions() {
		$this->model->bindModel(array('belongsTo' => array('Author')));

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('authors' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);

		$this->model->bindModel(array('belongsTo' => array(
			'Author' => array('conditions' => array('Author.user' => 'garrett'))
		)));
		$this->controller->viewVars = array();

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('authors' => array('4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test relatedModels by default gets lists only for belongsTo and HABTM assocaited models
 *
 * @return void
 */
	public function _testRelatedModelsDefaultModels() {
		$this->model->bindModel(array(
			'belongsTo' => array('Author'),
			'hasAndBelongsToMany' => array('Tag'),
			'hasMany' => array('Comment' => array('foreignKey' => 'article_id'))
		));

		$this->Crud->action('add')->config('relatedModels', true);
		$this->assertEquals(array('Author', 'Tag'), $this->Crud->listener('RelatedModels')->models('add'));
	}

/**
 * Test relatedModels with Tree Behavior attached
 *
 * @return void
 */
	public function _testRelatedModelsWithTree() {
		$FlagTree = ClassRegistry::init('FlagTree');
		$FlagTree->Behaviors->attach('Tree', array('scope' => array('FlagTree.flag' => 0)));
		$FlagTree->save(array('name' => 'Node 1.1', 'flag' => 0));
		$FlagTree->create();
		$FlagTree->save(array('name' => 'Node 1.2', 'parent_id' => 1, 'flag' => 0));
		$list = $FlagTree->generateTreeList();

		$this->model->bindModel(array('belongsTo' => array('FlagTree')));

		$this->Crud->action('add')->config('relatedModels', true);
		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('flagTrees' => $list);
		$this->assertEquals($expectedVars, $vars);

		$FlagTree->Behaviors->detach('Tree');
		$FlagTree->Behaviors->attach('Tree', array('scope' => array('FlagTree.flag' => 1)));
		$FlagTree->create();
		$FlagTree->save(array('name' => 'Node 2.1', 'flag' => 1));
		$FlagTree->create();
		$FlagTree->save(array('name' => 'Node 2.2', 'parent_id' => 3, 'flag' => 1));
		$list = $FlagTree->generateTreeList(array('FlagTree.flag' => 1));
		$this->assertEquals(array('3' => 'Node 2.1', '4' => '_Node 2.2'), $list);

		$this->controller->viewVars = array();
		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$expectedVars = array('flagTrees' => $list);
		$this->assertEquals($expectedVars, $vars);
	}

}
