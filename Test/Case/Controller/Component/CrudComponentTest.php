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
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
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
				'view',

				'admin_index',
				'admin_add',
				'admin_edit',
				'admin_delete',
				'admin_view'
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
		return $this->getSubject($additional);
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

		$this->request = new CakeRequest(null, false);
		$this->request->params['controller'] = 'posts';
		$this->request->params['action'] = 'index';

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
				'delete',

				'admin_index',
				'admin_add',
				'admin_edit',
				'admin_delete',
				'admin_view'
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

		$CrudAction = $this->Crud->getAction('delete');
		$CrudAction->config('secureDelete', true);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$CrudAction->config('validateId', 'integer');
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

		$CrudAction = $this->Crud->getAction('delete');
		$CrudAction->config('secureDelete', true);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return false; }
		));

		$this->controller->request->addDetector('post', array(
			'callback' => function() { return true; }
		));

		$CrudAction->config('validateId', 'integer');
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

		$CrudAction = $this->Crud->getAction('delete');
		$CrudAction->config('secureDelete', false);

		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return false; }
		));

		$this->controller->request->addDetector('post', array(
			'callback' => function() { return true; }
		));

		$CrudAction->config('validateId', 'integer');
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

		$this->Crud->getAction('edit')->config('validateId', 'notUuid');
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

		$this->Crud->getAction('view')->config('validateId', 'notUuid');
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
 * testvalidateIdIntValid
 */
	public function testvalidateIdIntValid() {
		return; // TODO: Fix
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
		return; // TODO: Fix
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
		return; // TODO: Fix
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
		return; // TODO: Fix
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
 * Test the default settings match the expected
 *
 * @return void
 */
	public function testCustomFindDefaults() {
		$this->assertEquals('all',   $this->Crud->getAction('index')->findMethod());
		$this->assertEquals('first', $this->Crud->getAction('add')->findMethod());
		$this->assertEquals('first', $this->Crud->getAction('edit')->findMethod());
		$this->assertEquals('count', $this->Crud->getAction('delete')->findMethod());

		$this->assertEquals('all', $this->Crud->getAction('admin_index')->findMethod());
		$this->assertEquals('first', $this->Crud->getAction('admin_add')->findMethod());
		$this->assertEquals('first', $this->Crud->getAction('admin_edit')->findMethod());
		$this->assertEquals('count', $this->Crud->getAction('admin_delete')->findMethod());
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

		$this->Crud->mapAction('show_all', 'index');
		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));
		$this->Crud->executeAction('index');
	}


/**
 * Test default saveAll options works when modified
 *
 * @return void
 */
	public function testGetSaveAllOptionsDefaults() {
		$CrudAction = $this->Crud->getAction('add');

		$expected = array('validate' => 'first', 'atomic' => true);
		$value = $CrudAction->config('saveOptions');
		$this->assertEqual($value, $expected);

		$CrudAction->config('saveOptions.atomic', true);
		$expected = array('validate' => 'first', 'atomic' => true);
		$value = $CrudAction->config('saveOptions');
		$this->assertEqual($value, $expected);

		$CrudAction->config('saveOptions', array('fieldList' => array('hello')));
		$expected = array('validate' => 'first', 'atomic' => true, 'fieldList' => array('hello'));
		$value = $CrudAction->config('saveOptions');
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
		$value = $this->Crud->getAction('add')->saveOptions();
		$this->assertEqual($value, $expected);

		$this->Crud->getAction('add')->saveOptions(array('atomic' => false));
		$expected = array('validate' => 'first', 'atomic' => false);
		$value = $this->Crud->getAction('add')->saveOptions();
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
