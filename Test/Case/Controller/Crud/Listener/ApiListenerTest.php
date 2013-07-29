<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('ApiListener', 'Crud.Controller/Crud/Listener');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ApiListenerTest extends CakeTestCase {

	protected $_config;

	public function setUp() {
		parent::setUp();
		$this->_config = Configure::read();
	}

	public function tearDown() {
		parent::tearDown();
		Configure::write($this->_config);
		CakePlugin::unload('TestPlugin');
	}

	public function testInitNotIsAPi() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts'));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$apiListener->init($event);

		//Testing detectors
		$subject->request->expects($this->at(0))
			->method('accepts')
			->with('application/json')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('accepts')
			->with('application/json')
			->will($this->returnValue(false));

		$subject->request->expects($this->at(2))
			->method('accepts')
			->with('text/xml')
			->will($this->returnValue(false));

		$subject->request->expects($this->at(3))
			->method('accepts')
			->with('text/xml')
			->will($this->returnValue(true));

		$this->assertTrue($subject->request->is('json'));
		$this->assertFalse($subject->request->is('json'));

		$subject->request->params['ext'] = 'json';
		$this->assertTrue($subject->request->is('json'));
		$this->assertTrue($subject->request->is('api'));

		$this->assertFalse($subject->request->is('xml'));
		$this->assertTrue($subject->request->is('xml'));

		$subject->request->params['ext'] = 'xml';
		$this->assertTrue($subject->request->is('xml'));
		$this->assertTrue($subject->request->is('api'));
	}

/**
 * Returns a list of actions and their http method
 *
 * @return void
 */
	public function actionsProvider() {
		return array(
			array('index', 'get'),
			array('view', 'get'),
			array('admin_index', 'get'),
			array('admin_view', 'get'),
			array('add', 'post'),
			array('admin_add', 'post'),
			array('edit', 'put'),
			array('admin_edit', 'put'),
			array('delete', 'delete'),
			array('admin_delete', 'delete')
		);
	}

/**
 * Tests initialization and expect no error
 *
 * @dataProvider actionsProvider
 * @return void
 */
	public function testIniIsAPI($action, $method) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->response = $this->getMock('CakeResponse');
		$subject->action = $subject->request->action = $action;

		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$subject->request->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('is')
			->with($method)
			->will($this->returnValue(true));
		$apiListener->init($event);
		$this->assertEquals('Crud.CrudExceptionRenderer', Configure::read('Exception.renderer'));
		$this->assertTrue(class_exists('CrudExceptionRenderer'));
	}

/**
 * Tests initialization with worng match of action and method
 *
 * @dataProvider actionsProvider
 * @expectedException MethodNotAllowedException
 * @return void
 */
	public function testIniError($action, $method) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->response = $this->getMock('CakeResponse');
		$subject->action = $subject->request->action = $action;

		$apiListener = new ApiListener($subject);
		$event = new CakeEvent('Crud.init', $subject);

		$subject->request->expects($this->at(0))
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->request->expects($this->at(1))
			->method('is')
			->with($method)
			->will($this->returnValue(false));
		$apiListener->init($event);
	}

/**
 * Tests that the function is not run if it is not an API call
 *
 * @return void
 */
	public function testAfterSaveNotAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));
		$subject->controller->expects($this->never())->method('set');
		$event = new CakeEvent('Crud.init', $subject);
		$apiListener->afterSave($event);
	}

/**
 * Returns the 2 possible states for subject->created
 *
 * @return void
 */
	public function createdProvider() {
		return array(
			array(true, 'once'),
			array(false, 'once')
		);
	}

/**
 * Test the response mangling after saving a record and is an API call
 *
 * @dataProvider createdProvider
 * @return void
 */
	public function testAfterSaveSuccess($created, $matcher) {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->controller->RequestHandler = $this->getMock('stdClass', array('viewClassMap', 'renderAs'));
		$subject->controller->RequestHandler->ext = 'json';
		$subject->response = $this->getMock('CakeResponse');
		$subject->crud = $this->getMock('stdClass', array('action'));
		$action = $this->getMock('stdClass', array('config'));
		$action
			->expects($this->at(0))
			->method('config')
			->with('serialize')
			->will($this->returnValue(array()));
		$subject->crud
			->expects($this->once())
			->method('action')
			->with()
			->will($this->returnValue($action));
		$apiListener = new ApiListener($subject);

		$subject->request
			->expects($this->any())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = true;
		$subject->controller
			->expects($this->at(0))
			->method('set')
			->with('success', true);

		$subject->model = new Model(array('alias' => 'Thing'));
		$subject->id = 100;
		$subject->controller
			->expects($this->at(1))
			->method('set')
			->with('data', array('Thing' => array('id' => 100)));

		$subject->controller
			->expects($this->once())
			->method('render')
			->will($this->returnValue($subject->response));

		$subject->controller->RequestHandler
			->expects($this->at(0))
			->method('viewClassMap')
			->with('json', 'Crud.CrudJson');

		$subject->controller->RequestHandler
			->expects($this->at(1))
			->method('viewClassMap')
			->with('xml', 'Crud.CrudXml');

		$subject->controller->RequestHandler
			->expects($this->at(2))
			->method('renderAs')
			->with($subject->controller, $subject->controller->RequestHandler->ext);

		$subject->created = $created;
		$expect = $subject->response
			->expects($this->{$matcher}())
			->method('statusCode');

		if ($created) {
			$expect->with(201);
		}

		$subject->response->expects($this->once())->method('header')
			->with('Location', Router::url(array('action' => 'view', 100), true));

		$event = new CakeEvent('Crud.afterSave', $subject);
		$result = $apiListener->afterSave($event);
		$this->assertSame($subject->response, $result);
	}

/**
 * Tests afterSave method when some validation errors occurred
 *
 * @return void
 */
	public function testAfterSaveNotSuccess() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = false;
		$subject->controller->expects($this->at(0))
			->method('set')
			->with('success', false);

		$subject->response->expects($this->once())->method('statusCode')->with(400);

		$subject->model = new Model(array('alias' => 'Thing'));
		$subject->model->validationErrors = array('field' => 'An error');
		$subject->controller->expects($this->at(1))
			->method('set')
			->with('data', $subject->model->validationErrors);

		$subject->controller->expects($this->never())->method('render');
		$event = new CakeEvent('Crud.afterSave', $subject);
		$this->assertNull($apiListener->afterSave($event));
	}

/**
 * Tests that afterDelete logic is not run if the call is not API
 *
 * @return void
 */
	public function testAfterDeleteNotAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));
		$subject->controller->expects($this->never())->method('set');
		$event = new CakeEvent('Crud.afterDelete', $subject);
		$apiListener->afterDelete($event);
	}

/**
 * Tests afterDelete logic
 *
 * @return void
 */
	public function testAfterDelete() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->controller->RequestHandler = $this->getMock('stdClass', array('viewClassMap', 'renderAs'));
		$subject->controller->RequestHandler->ext = 'json';
		$subject->response = $this->getMock('CakeResponse');
		$subject->crud = $this->getMock('stdClass', array('action'));
		$action = $this->getMock('stdClass', array('config'));
		$action
			->expects($this->at(0))
			->method('config')
			->with('serialize')
			->will($this->returnValue(array()));
		$subject->crud
			->expects($this->once())
			->method('action')
			->with()
			->will($this->returnValue($action));
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->any())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$subject->success = true;
		$subject->controller
			->expects($this->at(0))
			->method('set')
			->with('_serialize', array('success', 'data'));

		$subject->controller
			->expects($this->at(1))
			->method('set')
			->with('success', true);

		$subject->controller
			->expects($this->at(2))
			->method('set')
			->with('data', null);

		$subject->controller->expects($this->once())
			->method('render')
			->will($this->returnValue($subject->response));

		$event = new CakeEvent('Crud.afterDelete', $subject);
		$this->assertEquals($subject->response, $apiListener->afterDelete($event));
	}

/**
 * Tests that beforeRender logic is not run if the call is not API
 *
 * @return void
 */
	public function testBeforeRenderNoAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));
		$subject->controller->expects($this->never())->method('set');
		$event = new CakeEvent('Crud.beforeRender', $subject);
		$apiListener->beforeRender($event);
	}

/**
 * Tests that beforeRender logic is not run if the call is not API
 *
 * @return void
 */
	public function testBeforeRenderAPI() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->controller->RequestHandler = $this->getMock('RequestHandlerComponent', array('viewClassMap', 'renderAs'));
		$subject->controller->RequestHandler->ext = 'json';
		$subject->response = $this->getMock('CakeResponse');

		$subject->crud = $this->getMock('stdClass', array('action'));
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$action = $this->getMock('stdClass', array('config', 'viewVar'));
		$subject->crud->expects($this->once())->method('action')->will($this->returnValue($action));
		$action->expects($this->once())
			->method('config')
			->with('serialize')
			->will($this->returnValue(array()));
		$action->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('items'));

		$subject->controller->expects($this->once())
			->method('set')
			->with('_serialize', array('data' => 'items', 'success'));

		$subject->controller->RequestHandler->expects($this->at(0))
			->method('viewClassMap')
			->with('json', 'Crud.CrudJson');
		$subject->controller->RequestHandler->expects($this->at(1))
			->method('viewClassMap')
			->with('xml', 'Crud.CrudXml');
		$subject->controller->RequestHandler->expects($this->once())
			->method('renderAs')
			->with($subject->controller, 'json');
		$event = new CakeEvent('Crud.beforeRender', $subject);
		$apiListener->beforeRender($event);
	}

	public function testChangingViewVarWillReflectSerialize() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set'), array($subject->request));
		$subject->controller->RequestHandler = $this->getMock('RequestHandlerComponent', array('viewClassMap', 'renderAs'));
		$subject->controller->RequestHandler->ext = 'json';
		$subject->response = $this->getMock('CakeResponse');

		$subject->crud = $this->getMock('stdClass', array('action'));
		$apiListener = new ApiListener($subject);

		$subject->request->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$action = $this->getMock('stdClass', array('config', 'viewVar'));
		$subject->crud->expects($this->once())->method('action')->will($this->returnValue($action));
		$action->expects($this->once())
			->method('config')
			->with('serialize')
			->will($this->returnValue(array()));
		$action->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('something_else'));

		$subject->controller->expects($this->once())
			->method('set')
			->with('_serialize', array('data' => 'something_else', 'success'));

		$subject->controller->RequestHandler->expects($this->at(0))
			->method('viewClassMap')
			->with('json', 'Crud.CrudJson');
		$subject->controller->RequestHandler->expects($this->at(1))
			->method('viewClassMap')
			->with('xml', 'Crud.CrudXml');
		$subject->controller->RequestHandler->expects($this->once())
			->method('renderAs')
			->with($subject->controller, 'json');
		$event = new CakeEvent('Crud.beforeRender', $subject);
		$apiListener->beforeRender($event);
	}

/**
 * Tests implemented events
 *
 * @return void
 */
	public function testImplementeEvents() {
		$subject = $this->getMock('CrudSubject');
		$apiListener = new ApiListener($subject);
		$expected = array(
			'Crud.init' => array('callable' => 'init', 'priority' => 10),
			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 100),
			'Crud.afterSave' => array('callable' => 'afterSave', 'priority' => 100),
			'Crud.afterDelete' => array('callable' => 'afterDelete', 'priority' => 100),
			'Crud.setFlash' => array('callable' => 'setFlash', 'priority' => 100)
		);
		$this->assertEquals($expected, $apiListener->implementedEvents());
	}

/**
 * testFlashMessageSupressed
 *
 * The API listener should suppress flash messages
 * if the request is "API"
 *
 * @return void
 */
	public function testFlashMessageSupressed() {
		$Request = new CakeRequest();
		$Request->addDetector('api', array('callback' => function() { return true; }));

		$subject = new CrudSubject(array('request' => $Request));

		$apiListener = new ApiListener($subject);

		$event = new CakeEvent('Crud.setFlash', $subject);
		$apiListener->setFlash($event);

		$stopped = $event->isStopped();
		$this->assertTrue($stopped, 'Set flash event is expected to be stopped');
	}

/**
 * testFlashMessageNotSupressed
 *
 * The API listener should not suppress flash messages
 * if the request isn't "API"
 *
 * @return void
 */
	public function testFlashMessageNotSupressed() {
		$Request = new CakeRequest();
		$Request->addDetector('api', array('callback' => function() { return false; }));

		$subject = new CrudSubject(array('request' => $Request));

		$apiListener = new ApiListener($subject);

		$event = new CakeEvent('Crud.setFlash', $subject);
		$apiListener->setFlash($event);

		$stopped = $event->isStopped();
		$this->assertFalse($stopped, 'Set flash event is expected not to be stopped');
	}

/**
 * testMapResources
 *
 * Passing no argument, should map all of the app's controllers
 */
	public function testMapResources() {
		$path = CAKE . 'Test' . DS . 'test_app' . DS . 'Controller' . DS;
		App::build(array(
			'Controller' => array($path)
		), App::RESET);

		Router::reload();
		Router::$routes = array();

		ApiListener::mapResources();

		$expected = array(
			'GET index /pages',
			'GET view /pages/:id',
			'POST add /pages',
			'PUT edit /pages/:id',
			'DELETE delete /pages/:id',
			'POST edit /pages/:id',
			'GET index /test_apps_error',
			'GET view /test_apps_error/:id',
			'POST add /test_apps_error',
			'PUT edit /test_apps_error/:id',
			'DELETE delete /test_apps_error/:id',
			'POST edit /test_apps_error/:id',
			'GET index /tests_apps',
			'GET view /tests_apps/:id',
			'POST add /tests_apps',
			'PUT edit /tests_apps/:id',
			'DELETE delete /tests_apps/:id',
			'POST edit /tests_apps/:id',
			'GET index /tests_apps_posts',
			'GET view /tests_apps_posts/:id',
			'POST add /tests_apps_posts',
			'PUT edit /tests_apps_posts/:id',
			'DELETE delete /tests_apps_posts/:id',
			'POST edit /tests_apps_posts/:id'
		);
		$return = $this->_currentRoutes();

		$this->assertSame($expected, $return, 'test_app contains Pages, TestAppsError, TestApps and TestsAppsPosts controllers - there should be rest routes for all of these');
	}

/**
 * Passing a plugin name should map only for that plugin
 *
 */
	public function testMapResourcesPlugin() {
		$path = CAKE . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS;
		App::build(array(
			'Plugin' => array($path)
		), App::RESET);
		CakePlugin::load('TestPlugin');

		Router::reload();
		Router::$routes = array();

		ApiListener::mapResources('TestPlugin');

		$expected = array(
			'GET index /test_plugin/test_plugin',
			'GET view /test_plugin/test_plugin/:id',
			'POST add /test_plugin/test_plugin',
			'PUT edit /test_plugin/test_plugin/:id',
			'DELETE delete /test_plugin/test_plugin/:id',
			'POST edit /test_plugin/test_plugin/:id',
			'GET index /test_plugin/tests',
			'GET view /test_plugin/tests/:id',
			'POST add /test_plugin/tests',
			'PUT edit /test_plugin/tests/:id',
			'DELETE delete /test_plugin/tests/:id',
			'POST edit /test_plugin/tests/:id',
		);
		$return = $this->_currentRoutes();

		$this->assertSame($expected, $return, 'test plugin contains a test plugin and tests controller');
	}

/**
 * _currentRoutes
 *
 * Return current route definitions in a very simple format for comparison purposes
 *
 * @return array
 */
	protected function _currentRoutes() {
		$return = array();

		foreach (Router::$routes as $route) {
			$return[] = $route->defaults['[method]'] .
				' ' . $route->defaults['action'] .
				' ' . $route->template;
		}

		return $return;
	}
}
