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

	protected $log = array();

	public function dispatch($event) {
		$this->log[]= array(
			'name' => $event->name(),
			'subject' => $event->subject()
		);
		parent::dispatch($event);
	}

	public function getLog($params = array()) {
		$params += array('clear' => true, 'format' => 'names');

		$log = $this->log;

		if ($params['format'] === 'names') {
			$return = array();
			foreach ($log as $entry) {
				$return[] = $entry['name'];
			}
			$log = $return;
		}

		if ($params['clear']) {
			$this->log = array();
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
		$this->Crud->executeAction();
	}
}

/**
 * TestCrudComponent
 *
 * Expose protected methods so we can test them in isolation
 */
class TestCrudComponent extends CrudComponent {

/**
 * Test visibility wrapper
 */
	public function testGetSubject($additional = array()) {
		return $this->_getSubject($additional);
	}

/**
 * Test visibility wrapper
 */
	public function testRedirect($subject, $url = null) {
		return $this->_redirect($subject, $url);
	}

/**
 * Test visibility wrapper
 */
	public function testValidateId($id) {
		return $this->_validateId($id);
	}

/**
 * test visibility wrapper
 */
	public function testGetFindMethod($action = null, $default = null) {
		return parent::_getFindMethod($action, $default);
	}

/**
 * test visibility wrapper
 */
	public function detectPrimaryKeyFieldType() {
		return parent::_detectPrimaryKeyFieldType();
	}

/**
 * test visibility wrapper
 */
	public function getSaveAllOptions($action = null) {
		return parent::_getSaveAllOptions($action);
	}

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
		require_once('models.php');

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
 * testGetIdFromRequest
 *
 * Check that numeric and uuids are returned
 */
	public function testGetIdFromRequest() {
		$this->request->params['pass'][0] = '1';
		$id = $this->Crud->getIdFromRequest();
		$this->assertSame('1', $id);

		$this->request->params['pass'][0] = 1;
		$id = $this->Crud->getIdFromRequest();
		$this->assertSame(1, $id);

		$this->request->params['pass'][0] = '12345678-1234-1234-1234-123456789012';
		$id = $this->Crud->getIdFromRequest();
		$this->assertSame('12345678-1234-1234-1234-123456789012', $id);
	}

/**
 * testGetIdFromRequestEmpty
 *
 * None of these values should be returned
 */
	public function testGetIdFromRequestEmpty() {
		$id = $this->Crud->getIdFromRequest();
		$this->assertNull($id);

		$this->request->params['pass'][0] = '';
		$id = $this->Crud->getIdFromRequest();
		$this->assertNull($id);

		$this->request->params['pass'][0] = 0;
		$id = $this->Crud->getIdFromRequest();
		$this->assertNull($id);

		$this->request->params['pass'][0] = '0';
		$id = $this->Crud->getIdFromRequest();
		$this->assertNull($id);
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

		$this->Crud->config('secureDelete', false);

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

		$this->request->params['named']= array();

		$this->Crud->executeAction('index');

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterPaginate', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterPaginate event triggered");
	}

/**
 * testRedirect
 *
 * TODO this test isn't testing anything
 */
	public function testRedirect() {
		$subject = $this->Crud->testGetSubject();
		$this->Crud->testRedirect($subject);
	}

/**
 * testvalidateIdIntValid
 */
	public function testvalidateIdIntValid() {
		$this->controller->expects($this->never())->method('redirect');

		$this->Crud->settings['validateId'] = 'notUuid';

		$id = 1;
		$return = $this->Crud->testValidateId($id, 'int');
		$this->assertTrue($return, "Expected id $id to be accepted, it was rejected");
	}

/**
 * testvalidateIdIntInvalid
 */
	public function testvalidateIdIntInvalid() {
		$this->controller->expects($this->once())->method('redirect');

		$this->Crud->settings['validateId'] = 'notUuid';

		$id = 'abc';
		$return = $this->Crud->testValidateId($id, 'int');
		$this->assertSame(get_class($return), 'CakeResponse');
	}

/**
 * testvalidateIdUUIDValid
 */
	public function testvalidateIdUUIDValid() {
		$this->controller->expects($this->never())->method('redirect');

		$this->Crud->settings['validateId'] = 'uuid';

		$id = String::uuid();
		$return = $this->Crud->testValidateId($id);
		$this->assertTrue($return, "Expected id $id to be accepted, it was rejected");
	}

/**
 * testvalidateIdUUIDInvalid
 */
	public function testvalidateIdUUIDInvalid() {
		$this->Crud->settings['validateId'] = 'uuid';
		$this->controller->expects($this->once())->method('redirect');

		$id = 123;
		$return = $this->Crud->testValidateId($id, 'int');
		$this->assertSame(get_class($return), 'CakeResponse');
	}

/**
 * Tests on method for beforePaginateEvent
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.beforePaginate called
 * @return void
 */
	public function testOnBeforePaginateString() {
		$this->Crud->on('beforePaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Tests on method for afterPaginate
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 */
	public function testOnAfterPaginateString() {
		$this->Crud->on('afterPaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Tests on method for afterPaginate with full event name
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 */
	public function testOnAfterPaginateFullNameString() {
		$this->Crud->on('Crud.afterPaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
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
		$this->Crud->settings['relatedLists']['add'] = array('Author');
		$this->Crud->settings['relatedLists']['admin_add'] = array('Author');

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
		$this->Crud->getListener('related')->map(array('Tag'), 'default');
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
		$this->Crud->getListener('related')->map(array('Tag'), 'default');
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

		$this->Crud->getListener('related')->map(false, 'default');
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

		$this->Crud->getListener('related')->map(false, 'default');
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

		$this->Crud->getListener('related')->map(true, 'default');
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

		$this->Crud->getListener('related')->map(true, 'default');
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
		$this->Crud->settings['validateId'] = 'integer';
		$this->model->bindModel(array('belongsTo' => array('Author')), false);

		$this->Crud->mapAction('modify_action', 'edit');
		$this->Crud->getListener('related')->map(true);
		$this->assertEquals(array('Author'), $this->Crud->getListener('related')->models('modify_action'));

		$this->Crud->executeAction('modify_action', array(1));

		$vars = $this->controller->viewVars;
		$expectedVars = array('authors' => array(1 => '1', 2 => '2', '3' => '3', '4' => '4'));
		$this->assertEquals($expectedVars, $vars);
	}

/**
 * Test the default settings match the expected
 *
 * @return void
 */
	public function testCustomFindDefaults() {
		$this->assertEquals('all', $this->Crud->testGetFindMethod('index'));
		$this->assertEquals(null, $this->Crud->testGetFindMethod('add'));
		$this->assertEquals('first', $this->Crud->testGetFindMethod('edit'));
		$this->assertEquals('count', $this->Crud->testGetFindMethod('delete'));

		$this->assertEquals('all', $this->Crud->testGetFindMethod('admin_index'));
		$this->assertEquals(null, $this->Crud->testGetFindMethod('admin_add'));
		$this->assertEquals('first', $this->Crud->testGetFindMethod('admin_edit'));
		$this->assertEquals('count', $this->Crud->testGetFindMethod('admin_delete'));
	}

/**
 * Test if custom finds are changed when re-mapped
 *
 * @return void
 */
	public function testCustomFindChanged() {
		$this->Crud->mapFindMethod('index', 'custom_find');
		$this->assertEquals('custom_find', $this->Crud->testGetFindMethod('index'));

		$this->Crud->mapFindMethod('index', 'all');
		$this->assertEquals('all', $this->Crud->testGetFindMethod('index'));
	}

/**
 * Test that the default pagination settings match, bot for 2.3 and < 2.2
 *
 * @return void
 */
	public function testCustomFindPaginationDefaultNoAlias() {
		$this->Crud->executeAction('index');

		$this->assertEquals('all', $this->controller->paginate[0]);
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

		$this->assertTrue(empty($this->controller->paginate[0]));
		$this->assertTrue(empty($this->controller->paginate['findType']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample'][0]));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']['findType']));
		$this->assertEquals(array('order' => array('name' => 'desc'), 0 => 'all', 'findType' => 'all'), $this->controller->paginate['CrudExample']);
	}

/**
 * Test if custom pagination works - for published posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomPublished() {
		$this->Crud->mapFindMethod('index', 'published');
		$this->Crud->executeAction('index');
		$this->assertEquals('published', $this->controller->paginate[0]);
		$this->assertEquals('published', $this->controller->paginate['findType']);
		$this->assertEquals(3, sizeof($this->controller->viewVars['items']));
	}

/**
 * Test if custom pagination works - for unpublished posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomUnpublished() {
		$this->Crud->mapFindMethod('index', 'unpublished');
		$this->Crud->executeAction('index');
		$this->assertEquals('unpublished', $this->controller->paginate[0]);
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, sizeof($this->controller->viewVars['items']));
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
		$this->assertEquals('unpublished', $this->controller->paginate[0]);
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, sizeof($this->controller->viewVars['items']));
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
		$this->assertSame('2', $this->request->data['CrudExample']['id']);
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
 * @expectedException RuntimeException
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

		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));

		$this->Crud->executeAction('index');
	}

/**
 * Test if enableRelatedList works with a normal Crud action
 *
 * @return void
 */
	public function testEnableRelatedListStringForIndexAction() {
		$this->Crud->getListener('related')->map('Tag', 'default');
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
		$this->Crud->getListener('related')->map(array('Tag', 'Author'), 'default');
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
		$this->assertSame(2, sizeof($items), 'beforePaginate needs to have an effect on the pagination');
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

		$this->controller->paginate = array('conditions' => array(1 => 2));
		$this->Crud->executeAction('index');
		$this->assertSame(array(1 => 2), $Paginator->settings['conditions']);

		$Paginator->settings = array('conditions' => array(2 => 3));
		$this->Crud->executeAction('index');
		$this->assertSame(array(1 => 2), $Paginator->settings['conditions'], "Pagination settings from controller should always trump Paginator->settings");

		$Paginator->settings = array('conditions' => array(2 => 3));
		$this->controller->paginate = array();
		$this->Crud->executeAction('index');
		$this->assertSame(array(2 => 3), $Paginator->settings['conditions']);
	}

	public function testPaginationWithIterator() {
		$this->controller->paginate = array('limit' => 10);

		$this->Crud->on('afterPaginate', function(CakeEvent $e) {
			$e->subject->items = new ArrayIterator($e->subject->items);
		});

		$this->Crud->executeAction('index');

		$this->assertNotEmpty($this->controller->viewVars);
		$this->assertNotEmpty($this->controller->viewVars['items']);
		$this->assertSame(3, sizeof($this->controller->viewVars['items']));

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
		Router::connect("/:action", array('controller' => 'crud_examples'));

		$this->Controller = $this->generate(
			'CrudExamples',
			array(
				'methods' => array('header', 'redirect', 'render'),
				'components' => array('Session'),
			)
		);
		$this->Controller->Crud->settings['translations']['name'] = 'Thingy';

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
 * testAddActionTranslated
 *
 * @return void
 */
	public function testAddActionTranslated() {
		$this->skipIf(!class_exists('Translation'), 'Test depends on the Translations plugin');

		$translatedMessage = 'El ejemplo ha sido creado con exito';
		Translation::update('Successfully created CrudExample', $translatedMessage, array('domain' => 'crud'));
		$this->assertSame($translatedMessage, __d('crud', 'Successfully created CrudExample'));

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
			->with($translatedMessage);

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
 * testAddActionTranslatedDefaultDomain
 *
 * Simulate the controller's components array having defined the default domain for crud messages
 * Verify that that is the default domain is used for the crud translations
 *
 * @return void
 */
	public function testAddActionTranslatedDefaultDomain() {
		$this->skipIf(!class_exists('Translation'), 'Test depends on the Translations plugin');

		$translatedMessage = 'eksemplet blev oprettet med succes';
		Translation::update('Successfully created CrudExample', $translatedMessage);
		$this->assertSame($translatedMessage, __d('default', 'Successfully created CrudExample'));

		Router::connect("/:action", array('controller' => 'crud_examples'));

		CrudExamplesController::$componentsArray['Crud.Crud']['translations']['domain'] = 'default';
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
			->with($translatedMessage);

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
 * Test that detecting the correct validation strategy for validateId
 * works as expected
 *
 * @return void
 */
	public function testDetectPrimaryKeyFieldType() {
		$this->model = $this->getMock('CrudExample', array('schema'));
		$this->controller->CrudExample = $this->model;

		$this->model
			->expects($this->at(0))
			->method('schema')
			->with('id')
			->will($this->returnValue(false));

		$this->model
			->expects($this->at(1))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 36, 'type' => 'string')));

		$this->model
			->expects($this->at(2))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 10, 'type' => 'integer')));

		$this->model
			->expects($this->at(3))
			->method('schema')
			->with('id')
			->will($this->returnValue(array('length' => 10, 'type' => 'string')));

		$this->assertFalse($this->Crud->detectPrimaryKeyFieldType());
		$this->assertSame('uuid', $this->Crud->detectPrimaryKeyFieldType());
		$this->assertSame('integer', $this->Crud->detectPrimaryKeyFieldType());
		$this->assertFalse($this->Crud->detectPrimaryKeyFieldType());
	}

/**
 * Test default saveAll options works when modified
 *
 * @return void
 */
	public function testGetSaveAllOptionsDefaults() {
		$expected = array('validate' => 'first', 'atomic' => true);
		$value = $this->Crud->getSaveAllOptions();
		$this->assertEqual($value, $expected);

		$this->Crud->config('saveAllOptions.default', array('atomic' => false));
		$expected = array('validate' => 'first', 'atomic' => false);
		$value = $this->Crud->getSaveAllOptions();
		$this->assertEqual($value, $expected);

		$this->Crud->config('saveAllOptions.default.atomic', true);
		$expected = array('validate' => 'first', 'atomic' => true);
		$value = $this->Crud->getSaveAllOptions();
		$this->assertEqual($value, $expected);

		$this->Crud->config('saveAllOptions.default', array('fieldList' => array('hello')));
		$expected = array('validate' => 'first', 'atomic' => true, 'fieldList' => array('hello'));
		$value = $this->Crud->getSaveAllOptions();
		$this->assertEqual($value, $expected);
	}

/**
 * Test that defining specific action configuration for saveAll takes
 * precedence over default configurations
 *
 * @return void
 */
	public function testGetSaveAllOptionsCustomAction() {
		$expected = array('validate' => 'first', 'atomic' => true);
		$value = $this->Crud->getSaveAllOptions('add');
		$this->assertEqual($value, $expected);

		$this->Crud->config('saveAllOptions.add', array('atomic' => false));
		$expected = array('validate' => 'first', 'atomic' => false);
		$value = $this->Crud->getSaveAllOptions('add');
		$this->assertEqual($value, $expected);
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
 * @expectedException RuntimeException
 * @expectedExceptionMessage No model loaded in the Controller by the name "Donkey". Please add it to $uses.
 */
	public function testSetModelPropertiesChangeModelForActionNotLoadedModel() {
		$this->Crud->setAction('index');
		$this->Crud->config('modelMap.index', 'Donkey');
		$this->Crud->setModelProperties();

		$this->assertSame('Donkey', $this->Crud->getModelName());
	}

}
