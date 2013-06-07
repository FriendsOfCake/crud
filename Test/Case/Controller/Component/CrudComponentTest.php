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
