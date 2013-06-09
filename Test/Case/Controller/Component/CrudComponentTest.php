<?php
App::uses('Router', 'Routing');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');

App::uses('CakeEventManager', 'Event');

App::uses('ComponentCollection', 'Controller');
App::uses('Component', 'Controller');
App::uses('CrudComponent', 'Crud.Controller/Component');

App::uses('Model', 'Model');

App::uses('Validation', 'Utility');

/**
 * TestCrudEventManager
 *
 * This manager class is used to replace the CakeEventManger instance.
 * As such, it becomes a global listener and is used to keep a log of
 * all events fired during the test
 */
class TestCrudEventManager extends CakeEventManager {

	protected $_log = array();

	public function dispatch($event) {
		$this->_log[] = array(
			'name' => $event->name(),
			'subject' => $event->subject()
		);
		parent::dispatch($event);
	}

	public function getLog($params = array()) {
		$params += array('clear' => true, 'format' => 'names');

		$log = $this->_log;

		if ($params['format'] === 'names') {
			$return = array();
			foreach ($log as $entry) {
				$return[] = $entry['name'];
			}
			$log = $return;
		}

		if ($params['clear']) {
			$this->_log = array();
		}

		return $log;
	}

}

class CrudExamplesController extends Controller {

	public static $componentsArray = array(
		'Session',
		'Crud.Crud' => array(
			'actions' => array(
				'index',
				'add',
				'edit',
				'delete',
				'view'
			)
		)
	);

/**
 * Make it possible to dynamically define the components array during tests
 *
 * @param mixed $request
 * @param mixed $response
 * @return void
 */
	public function __construct($request = null, $response = null) {
		$this->components = self::$componentsArray;

		return parent::__construct($request, $response);
	}

/**
 * add
 *
 * Used in the translations test
 *
 * @return void
 */
	public function add() {
		return $this->Crud->executeAction();
	}

}

/**
 * TestCrudComponent
 *
 * Expose protected methods so we can test them in isolation
 */
class TestCrudComponent extends CrudComponent {

/**
 * test visibility wrapper - access protected _modelName property
 */
	public function getModelName() {
		return $this->_modelName;
	}

/**
 * test visibility wrapper - call protected method _setModelProperties
 */
	public function setModelProperties() {
		return parent::_setModelProperties();
	}

/**
 * test visibility wrapper - allow on the fly change of action name
 */
	public function setAction($name) {
		$this->_action = $name;
	}

}

class CrudController extends Controller {

	public $paginate = array(
		'limit' => 1000
	);

}

/**
 * CrudComponentTestCase
 */
class CrudComponentTestCase extends ControllerTestCase {

/**
 * fixtures
 *
 * Use the core posts fixture to have something to work on.
 * What fixture is used is almost irrelevant, was chosen as it is simple
 */
	public $fixtures = array('core.post', 'core.author', 'core.tag', 'plugin.crud.posts_tag');

/**
 * setUp
 *
 * Setup the classes the crud component needs to be testable
 */
	public function setUp() {
		require_once ('models.php');

		parent::setUp();

		CakeEventManager::instance(new TestCrudEventManager());

		ConnectionManager::getDataSource('test')->getLog();

		$this->model = new CrudExample();

		$this->controller = $this->getMock(
			'CrudController',
			array('header', 'redirect', 'render', '_stop'),
			array(),
			'',
			false
		);
		$this->controller->name = 'CrudExamples';

		$this->request = new CakeRequest();
		$response = new CakeResponse();
		$this->controller->__construct($this->request, $response);
		$this->controller->methods = array();

		$Collection = new ComponentCollection();
		$Collection->init($this->controller);
		$settings = array(
			'actions' => array(
				'index',
				'add',
				'edit',
				'view',
				'delete'
			)
		);
		$this->controller->Components = $Collection;

		$this->Crud = $this->getMock(
			'TestCrudComponent',
			null,
			array($Collection, $settings)
		);

		$this->Crud->initialize($this->controller);
	}

/**
 * tearDown method
 */
	public function tearDown() {
		unset(
			$this->model,
			$this->request,
			$this->controller,
			$this->Crud
		);

		parent::tearDown();
	}

/**
 * testEnableAction
 */
	public function testEnableAction() {
		$this->Crud->mapAction('puppies', 'view', false);
		$this->Crud->enableAction('puppies');

		$result = $this->Crud->isActionMapped('puppies');
		$this->assertTrue($result);
	}

/**
 * testDisableAction
 */
	public function testDisableAction() {
		$this->Crud->disableAction('view');

		$result = $this->Crud->isActionMapped('view');
		$this->assertFalse($result);
	}

/**
 * testMapAction
 */
	public function testMapAction() {
		$this->Crud->mapAction('puppies', 'view');

		$result = $this->Crud->isActionMapped('puppies');
		$this->assertTrue($result);
	}

/**
 * testMapActionView
 */
	public function testMapActionView() {
		$this->controller
			->expects($this->once())
			->method('render');

		$this->Crud->mapActionView('view', 'cupcakes');
		$this->Crud->executeAction('view', array(1));
	}

/**
 * testIsActionMappedYes
 */
	public function testIsActionMappedYes() {
		$result = $this->Crud->isActionMapped('index');
		$this->assertTrue($result);

		$this->controller->action = 'edit';
		$this->Crud->initialize($this->controller);
		$result = $this->Crud->isActionMapped();
		$this->assertTrue($result);
	}

/**
 * testIsActionMappedNo
 */
	public function testIsActionMappedNo() {
		$result = $this->Crud->isActionMapped('puppies');
		$this->assertFalse($result);

		$this->controller->action = 'rainbows';
		$this->Crud->initialize($this->controller);
		$result = $this->Crud->isActionMapped();
		$this->assertFalse($result);
	}

/**
 * testAddActionGet
 *
 * Add should render the form template
 */
	public function testAddActionGet() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('add');

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('add', array($id));
	}

/**
 * testAddActionPost
 *
 * Create a post, check that the created row looks about right.
 * Check that there are 4 rows after calling (3 fixtures and one
 * new row)
 */
	public function testAddActionPost() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->controller->request->addDetector('post', array(
			'callback' => function() { return true; }
		));

		$this->controller->data = array(
			'CrudExample' => array(
				'title' => __METHOD__,
				'description' => __METHOD__,
				'author_id' => 0
			)
		);

		$this->Crud->executeAction('add', array());

		$this->assertNotSame(false, $this->model->id, "No row has been created");
		$this->assertSame(__METHOD__, $this->model->field('title'));

		$count = $this->model->find('count');
		$this->assertSame(4, $count);

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterSave', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterSave event triggered");
	}

/**
 * testDeleteActionExists
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteActionExists() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('delete', array($id));

		$count = $this->model->find('count', array('conditions' => array('id' => $id)));
		$this->assertSame(0, $count);

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.beforeDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteActionDoesNotExists
 *
 * If you try to delete something that doesn't exist - it should issue a recordNotFound event
 * TODO it should /not/ issue a beforeDelete event but it currently does
 */
	public function testDeleteActionDoesNotExists() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 42;
		$this->Crud->executeAction('delete', array($id));

		$count = $this->model->find('count');
		$this->assertSame(3, $count);

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.beforeDelete', $events);
		$this->assertSame(false, $index, "Crud.beforeDelete event triggered");

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "A none-existent row did not trigger a Crud.recordNotFount event");
	}

/**
 * testDeleteWorksWhenDELETEandSecureDelete
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteWorksWhenDELETEandSecureDelete() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->Crud->config('secureDelete', true);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$id = 1;

		$this->Crud->executeAction('delete', array($id));

		$count = $this->model->find('count', array('conditions' => array('id' => $id)));
		$this->assertSame(0, $count);

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.beforeDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteFailsWithPostAndSecureDeleteActive
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteFailsWithPostAndSecureDeleteActive() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->Crud->config('secureDelete', true);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return false; }
		));

		$this->controller->request->addDetector('post', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$id = 1;

		$this->Crud->executeAction('delete', array($id));

		$count = $this->model->find('count', array('conditions' => array('id' => $id)));
		$this->assertSame(1, $count);

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.beforeDelete', $events);
		$this->assertSame(false, $index, "There was a Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertSame(false, $index, "There was a Crud.beforeDelete event triggered");

		$index = array_search('Crud.setFlash', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * testDeleteWorksWithPostAndSecureDeleteDisabled
 *
 * Add a dummy detector to the request object so it says it's a delete request
 * Check the deleted row doesn't exist after calling delete and that the
 * before + after delete events are triggered
 */
	public function testDeleteWorksWithPostAndSecureDeleteActive() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->Crud->action('delete')->config('secureDelete', false);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return false; }
		));

		$this->controller->request->addDetector('post', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$id = 1;

		$this->Crud->executeAction('delete', array($id));

		$count = $this->model->find('count', array('conditions' => array('id' => $id)));
		$this->assertSame(0, $count);

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.beforeDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertNotSame(false, $index, "There was a Crud.beforeDelete event triggered");
	}

/**
 * testEditActionGet
 *
 * Do we get a call to render the form template?
 */
	public function testEditActionGet() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('edit');

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('edit', array($id));
	}

/**
 * testEditActionPost
 *
 * Simulating submitting a post form which just changes the title of the model
 * to the name of the method. Check the update is persisted to the db
 */
	public function testEditActionPost() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->controller->request->addDetector('put', array(
			'callback' => function() { return true; }
		));

		$this->controller->data = array(
			'CrudExample' => array(
				'id' => 1,
				'title' => __METHOD__
			)
		);

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('edit', array($id));

		$this->model->id = $id;
		$result = $this->model->field('title');
		$this->assertSame(__METHOD__, $result);

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterSave', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterSave event triggered");
	}

/**
 * testViewAction
 *
 * Make sure that there is a call to render the view template
 */
	public function testViewAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('view');

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('view', array($id));
	}

/**
 * testIndexAction
 *
 * Make sure that there is a call to render the index template
 */
	public function testIndexAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('index');

		$this->request->params['named'] = array();

		$this->Crud->executeAction('index');

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterPaginate', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterPaginate event triggered");
	}

/**
 * Tests on method for beforePaginateEvent
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Crud.beforePaginate called
 * @return void
 * @throws CakeException
 */
	public function testOnBeforePaginateString() {
		$this->Crud->on('beforePaginate', function($event) {
			throw new CakeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Test Paginator->settings updation in beforePaginate callback
 *
 * @return void
 */
	public function testPaginatorSettingsUpdation() {
		$this->controller->paginate = array(
			'CrudExample' => array(
				'paramType' => 'named',
				'order' => array('name' => 'desc')
			)
		);

		$this->Crud->on('beforePaginate', function($event) {
			unset($event->subject->controller->paginate);
			$event->subject->controller->Paginator->settings['CrudExample']['paramType'] = 'querystring';
		});

		$this->Crud->executeAction('index');

		$expected = array(
			'order' => array('name' => 'desc'),
			'paramType' => 'querystring',
			'findType' => 'all'
		);

		$this->assertEquals($expected, $this->controller->Paginator->settings['CrudExample']);
	}

/**
 * Tests on method for afterPaginate
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 * @throws CakeException
 */
	public function testOnAfterPaginateString() {
		$this->Crud->on('afterPaginate', function($event) {
			throw new CakeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Tests on method for afterPaginate with full event name
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 * @throws CakeException
 */
	public function testOnAfterPaginateFullNameString() {
		$this->Crud->on('Crud.afterPaginate', function($event) {
			throw new CakeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Test on method for on() with multiple events
 *
 * @return void
 */
	public function testOnOnWithArraySimple() {
		$result = array();
		$this->Crud->on(array('beforePaginate', 'beforeRender'), function($event) use (&$result) {
			$result[] = $event->name() . ' called';
		});
		$this->Crud->executeAction('index');

		$expected = array('Crud.beforePaginate called', 'Crud.beforeRender called');
		$this->assertSame($expected, $result);
	}

/**
 * Test on method for on() with multiple events
 *
 * @return void
 */
	public function testOnOnWithArrayComplex() {
		$result = array();
		$this->Crud->on(array('Crud.beforePaginate', 'beforeRender'), function($event) use (&$result) {
			$result[] = $event->name() . ' called';
		});
		$this->Crud->executeAction('index');

		$expected = array('Crud.beforePaginate called', 'Crud.beforeRender called');
		$this->assertSame($expected, $result);
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
	}

/**
 * Tests that by default Crud can select some models for each action to fetch related lists
 *
 * @return void
 */
	public function testFetchRelatedMapped() {
		$this->model->bindModel(array('belongsTo' => array('Author'), 'hasAndBelongsToMany' => array('Tag')), false);
		$this->Crud->action('add')->config('relatedLists', array('Author'));

		$expectedAuthors = array(1 => '1', 2 => '2', 3 => '3', 4 => '4');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedAuthors, $vars['authors']);
		$this->assertFalse(isset($vars['tags']));
		$this->controller->viewVars = array();
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
		$expectedTags = array(1 => '1', 2 => '2', 3 => '3');

		$this->Crud->executeAction('add');
		$vars = $this->controller->viewVars;
		$this->assertEquals($expectedTags, $vars['tags']);
		$this->assertFalse(isset($vars['authors']));
		$this->controller->viewVars = array();
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
		$this->Crud->getListener('related')->map(array('Tag'), 'delete');

		$this->Crud->executeAction('delete', array('1'));
		$vars = $this->controller->viewVars;
		$this->assertFalse(isset($vars['tags']));
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
 * Test the default settings match the expected
 *
 * @return void
 */
	public function testCustomFindDefaults() {
		$this->assertEquals('all', $this->Crud->action('index')->findMethod());
		$this->assertEquals('first', $this->Crud->action('add')->findMethod());
		$this->assertEquals('first', $this->Crud->action('edit')->findMethod());
		$this->assertEquals('count', $this->Crud->action('delete')->findMethod());
	}

/**
 * Test if custom finds are changed when re-mapped
 *
 * @return void
 */
	public function testCustomFindChanged() {
		$this->Crud->mapFindMethod('index', 'custom_find');
		$this->assertEquals('custom_find', $this->Crud->action('index')->findMethod());

		$this->Crud->mapFindMethod('index', 'all');
		$this->assertEquals('all', $this->Crud->action('index')->findMethod());
	}

/**
 * Test that the default pagination settings match, bot for 2.3 and < 2.2
 *
 * @return void
 */
	public function testCustomFindPaginationDefaultNoAlias() {
		$this->Crud->executeAction('index');

		$this->assertEquals('all', $this->controller->paginate['findType']);
	}

/**
 * Test that the default pagination settings match, bot for 2.3 and < 2.2
 *
 * @return void
 */
	public function testCustomFindPaginationDefaultWithAlias() {
		$this->controller->paginate = array(
			'CrudExample' => array(
				'order' => array('name' => 'desc')
			),
			'demo' => true
		);

		$this->Crud->executeAction('index');

		$this->assertTrue(empty($this->controller->paginate['findType']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']['findType']));
		$this->assertEquals(array('order' => array('name' => 'desc'), 'findType' => 'all'), $this->controller->paginate['CrudExample']);
	}

/**
 * Test if custom pagination works - for published posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomPublished() {
		$this->Crud->mapFindMethod('index', 'published');
		$this->Crud->executeAction('index');
		$this->assertEquals('published', $this->controller->paginate['findType']);
		$this->assertEquals(3, count($this->controller->viewVars['items']));
	}

/**
 * Test if custom pagination works - for unpublished posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomUnpublished() {
		$this->Crud->mapFindMethod('index', 'unpublished');
		$this->Crud->executeAction('index');
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, count($this->controller->viewVars['items']));
	}

/**
 * Test if custom pagination works when findType is changed from Controller
 * paginate property
 *
 * @return void
 */
	public function testCustomFindPaginationWithControllerFindMethod() {
		$this->controller->paginate = array('findType' => 'unpublished');
		$this->Crud->executeAction('index');
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, count($this->controller->viewVars['items']));
	}

/**
 * Test if custom finders work in edit
 *
 * @return void
 */
	public function testCustomFindEditPublished() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('edit', 'firstPublished');
		$this->Crud->executeAction('edit', array(2));

		$this->assertNotEmpty($this->request->data);
		$this->assertNotEmpty($this->request->data['CrudExample']);
		$this->assertSame('2', (string)$this->request->data['CrudExample']['id']);
	}

/**
 * Test if custom finders work in edit - part two
 *
 * @return void
 */
	public function testCustomFindEditUnpublished() {
		$this->controller->expects($this->once())->method('redirect');

		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('edit', 'firstUnpublished');
		$this->Crud->executeAction('edit', array(2));

		$this->assertTrue(empty($this->request->data));

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "There was no Crud.recordNotFound event triggered");
	}

/**
 * Test if custom finders work in delete
 *
 * @return void
 */
	public function testCustomFindDeletePublished() {
		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('delete', 'firstPublished');
		$this->Crud->executeAction('delete', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");

		$count = $this->model->find('count');
		$this->assertEquals(2, $count);
	}

/**
 * Test if custom finders work in delete - part 2
 *
 * @return void
 */
	public function testCustomFindDeleteUnpublished() {
		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('delete', 'firstUnpublished');
		$this->Crud->executeAction('delete', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeDelete', $events);
		$this->assertSame(false, $index, "Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertSame(false, $index, "Crud.afterDelete event triggered");

		$count = $this->model->find('count');
		$this->assertEquals(3, $count);
	}

/**
 * Test if custom finders work in view
 *
 * @return void
 */
	public function testCustomFindViewPublished() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('view', 'firstPublished');
		$this->Crud->executeAction('view', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeFind', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterFind', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * Test if custom finders work in view - part 2
 *
 * @return void
 */
	public function testCustomFindViewUnpublished() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('view', 'firstUnpublished');
		$this->Crud->executeAction('view', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "There was no Crud.recordNotFound event triggered");
	}

/**
 * Test if crud complains about unmapped actions
 *
 * @expectedException CakeException
 * @return void
 */
	public function testCrudWillComplainAboutUnmappedAction() {
		$this->Crud->executeAction('show_all');
	}

/**
 * Test if mapActionView with array yields the expected result
 *
 * @return void
 */
	public function testMapActionViewWithArrayNewAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('index');

		$this->Crud->mapAction('show_all', 'index');
		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));

		$this->Crud->executeAction('show_all');
	}

/**
 * Test if mapActionView with array yields the expected result
 *
 * @return void
 */
	public function testMapActionViewWithArrayIndexAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('overview');

		$this->Crud->mapAction('show_all', 'index');
		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));

		$this->Crud->executeAction('index');
	}

/**
 * Test if enableRelatedList works with a normal Crud action
 *
 * @return void
 */
	public function testEnableRelatedListStringForIndexAction() {
		$this->Crud->getListener('related')->map('Tag', 'index');
		$this->Crud->getListener('related')->enable('index');

		$expected = array('Tag');
		$value = $this->Crud->getListener('related')->models('index');
		$this->assertSame($expected, $value);

		$this->Crud->executeAction('index');

		$this->assertTrue(!empty($this->controller->viewVars['tags']));
		$this->assertTrue(!empty($this->controller->viewVars['items']));

		$this->assertSame(3, count($this->controller->viewVars['tags']));
		$this->assertSame(3, count($this->controller->viewVars['items']));
	}

/**
 * Test if enableRelatedList works with a normal Crud action
 *
 * @return void
 */
	public function testEnableRelatedListArrayForIndexAction() {
		$this->Crud->getListener('related')->map(array('Tag', 'Author'), 'index');
		$this->Crud->getListener('related')->enable(array('index'));

		$this->assertSame(array('Tag', 'Author'), $this->Crud->getListener('related')->models('index'));

		$this->Crud->executeAction('index');

		$this->assertTrue(!empty($this->controller->viewVars['tags']));
		$this->assertTrue(!empty($this->controller->viewVars['items']));
		$this->assertTrue(!empty($this->controller->viewVars['authors']));

		$this->assertSame(3, count($this->controller->viewVars['tags']));
		$this->assertSame(3, count($this->controller->viewVars['items']));
		$this->assertSame(4, count($this->controller->viewVars['authors']));
	}

	public function testIndexActionPaginationSettingsNotLost() {
		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(1000, $paging['CrudExample']['limit']);
	}

	public function testIndexActionPaginationSettingsCanBeOverwritten() {
		$this->controller->paginate = array('limit' => 11);

		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(11, $paging['CrudExample']['limit']);

		$this->assertSame(11, $this->controller->Components->load('Paginator')->settings['limit']);
	}

	public function testPersistDirectPaginatorSettingsWillNotBeCopied() {
		$Paginator = $this->controller->Components->load('Paginator');

		$Paginator->settings = array('limit' => 23);

		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(1000, $paging['CrudExample']['limit']);
		$this->assertNotSame(23, $Paginator->settings['limit']);
	}

	public function testOnBeforePaginateWithPaginatConditionsFromBeforePaginateCallback() {
		$Paginator = $this->controller->Components->load('Paginator');
		$this->Crud->on('beforePaginate', function($event) {
			$event->subject->controller->paginate['conditions'] = array('author_id' => 1);
		});

		$this->Crud->executeAction('index');

		$items = $this->controller->viewVars['items'];
		$this->assertSame(2, count($items), 'beforePaginate needs to have an effect on the pagination');
		$this->assertEquals(array('author_id' => 1), $Paginator->settings['conditions']);
	}

	public function testOnBeforePaginateWithPaginatLimitFromBeforePaginateCallback() {
		$Paginator = $this->controller->Components->load('Paginator');
		$this->Crud->on('beforePaginate', function($event) {
			$event->subject->controller->paginate['limit'] = 99;
		});

		$this->Crud->executeAction('index');

		$this->assertEquals(99, $Paginator->settings['limit']);
	}

	public function testIfConditionsPersistetInIndexAction() {
		$Paginator = $this->controller->Components->load('Paginator');

		$this->controller->paginate = array('conditions' => array('1 = 2'));
		$this->Crud->executeAction('index');
		$this->assertSame(array('1 = 2'), $Paginator->settings['conditions']);

		$Paginator->settings = array('conditions' => array('2 = 3'));
		$this->Crud->executeAction('index');
		$this->assertSame(array('1 = 2'), $Paginator->settings['conditions'], "Pagination settings from controller should always trump Paginator->settings");

		$Paginator->settings = array('conditions' => array('2 = 3'));
		$this->controller->paginate = array();
		$this->Crud->executeAction('index');
		$this->assertSame(array('2 = 3'), $Paginator->settings['conditions']);
	}

	public function testPaginationWithIterator() {
		$this->controller->paginate = array('limit' => 10);

		$this->Crud->on('afterPaginate', function(CakeEvent $e) {
			$e->subject->items = new ArrayIterator($e->subject->items);
		});

		$this->Crud->executeAction('index');

		$this->assertNotEmpty($this->controller->viewVars);
		$this->assertNotEmpty($this->controller->viewVars['items']);
		$this->assertSame(3, count($this->controller->viewVars['items']));

		$ids = Hash::extract($this->controller->viewVars['items'], '{n}.CrudExample.id');
		$this->assertEquals(array(1,2,3), $ids);
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
		//TODO: migrate Translations listener config to the new format in CrudAction
		$this->skipIf(true);
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);
		$this->Controller->Crud->initialize($this->Controller);
		$this->Controller->Crud->defaults('action', 'add', array(
			'translations' => array('name' => 'Thingy')
		));

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
		//TODO: migrate Translations listener config to the new format in CrudAction
		$this->skipIf(true);
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);
		$this->Controller->Crud->settings['translations']['create']['success']['message'] = "Yay!";

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

/**
 * Test that having no mapped model for an action,
 * just use the modelClass from the controller
 *
 * @return void
 */
	public function testSetModelPropertiesDefault() {
		$this->Crud->setAction('index');
		$this->Crud->setModelProperties();
		$this->assertSame('CrudExample', $this->Crud->getModelName());
	}

/**
 * Test that having mapped a custom model for an action,
 * the modelName will be as configured
 *
 * @return void
 */
	public function testSetModelPropertiesChangeModelForAction() {
		$this->controller->Donkey = new StdClass;

		$this->Crud->setAction('index');
		$this->Crud->config('modelMap.index', 'Donkey');
		$this->Crud->setModelProperties();

		$this->assertSame('Donkey', $this->Crud->getModelName());
	}

/**
 * Test that having mapped a custom model for an action,
 * but the custom model isn't loaded, will throw an exception
 *
 * @expectedException CakeException
 * @expectedExceptionMessage No model loaded in the Controller by the name "Donkey". Please add it to $uses.
 */
	public function testSetModelPropertiesChangeModelForActionNotLoadedModel() {
		$this->Crud->setAction('index');
		$this->Crud->config('modelMap.index', 'Donkey');
		$this->Crud->setModelProperties();

		$this->assertSame('Donkey', $this->Crud->getModelName());
	}

/**
 * Test that the build in action names can't be used
 * within other plugins
 *
 * @expectedException CakeException
 * @expectedExceptionMessage The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin
 * @return void
 */
	public function testBuildInCrudActionsCantBeUsedInOtherPluginsIndex() {
		$this->Crud->mapAction('test', 'Sample.Index');
	}

/**
 * Test that the build in action names can't be used
 * within other plugins
 *
 * @expectedException CakeException
 * @expectedExceptionMessage The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin
 * @return void
 */
	public function testBuildInCrudActionsCantBeUsedInOtherPluginsView() {
		$this->Crud->mapAction('test', 'Sample.View');
	}

/**
 * Test that the build in action names can't be used
 * within other plugins
 *
 * @expectedException CakeException
 * @expectedExceptionMessage The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin
 * @return void
 */
	public function testBuildInCrudActionsCantBeUsedInOtherPluginsAdd() {
		$this->Crud->mapAction('test', 'Sample.Add');
	}

/**
 * Test that the build in action names can't be used
 * within other plugins
 *
 * @expectedException CakeException
 * @expectedExceptionMessage The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin
 * @return void
 */
	public function testBuildInCrudActionsCantBeUsedInOtherPluginsEdit() {
		$this->Crud->mapAction('test', 'Sample.Edit');
	}

/**
 * Test that the build in action names can't be used
 * within other plugins
 *
 * @expectedException CakeException
 * @expectedExceptionMessage The build-in CrudActions (Index, View, Add, Edit and Delete) must be loaded from the Crud plugin
 * @return void
 */
	public function testBuildInCrudActionsCantBeUsedInOtherPluginsDelete() {
		$this->Crud->mapAction('test', 'Sample.Delete');
	}

/**
 * Test that Providing a CrudAction name that isn't in the
 * list of build-in once, will allow you to use it inside
 * another plugin.
 *
 * It's expected that the plugin CrudSample doesn't exist,
 * the App::uses() where the warning is raised is *after*
 * the check for the above build-in class names
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Plugin CrudSample could not be found.
 * @return void
 */
	public function testCustomCrudActionsCanBeUsedInPlugins() {
		$this->Crud->mapAction('test', 'CrudSample.MyDelete');
	}
}
