<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('RequestHandler', 'Controller/Component');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('ApiListener', 'Crud.Controller/Crud/Listener');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('IndexCrudAction', 'Crud.Controller/Crud/Action');

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

		$event = new CakeEvent('Crud.startup', $subject);
		$apiListener->startup($event);

		$event = new CakeEvent('Crud.init', $subject);
		$apiListener->initialize($event);

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
 * Tests initialization in API request
 *
 * @covers ApiListener::initialize
 * @return void
 */
	public function testInitialize() {
		$request = $this->getMock('CakeRequest', array('is'));
		$request->expects($this->once())->method('is')->with('api')->will($this->returnValue(true));

		$mockMethods = array('_request', 'registerExceptionHandler');
		$listener = $this->getMock('ApiListener', $mockMethods, array(new CrudSubject()));
		$listener->expects($this->once())->method('_request')->with()->will($this->returnValue($request));
		$listener->expects($this->once())->method('registerExceptionHandler')->with();
		$listener->initialize(new CakeEvent('Crud.init'));
	}

/**
 * Tests initialization in non-API request
 *
 * @covers ApiListener::initialize
 * @return void
 */
	public function testInitializeNotRequest() {
		$request = $this->getMock('CakeRequest', array('is'));
		$request->expects($this->once())->method('is')->with('api')->will($this->returnValue(false));

		$mockMethods = array('_request', 'registerExceptionHandler');
		$listener = $this->getMock('ApiListener', $mockMethods, array(new CrudSubject()));
		$listener->expects($this->once())->method('_request')->with()->will($this->returnValue($request));
		$listener->expects($this->never())->method('registerExceptionHandler');
		$listener->initialize(new CakeEvent('Crud.init'));
	}

/**
 * Tests that the function is not run if it is not an API call
 *
 * @covers ApiListener::afterSave
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
 * @covers ApiListener::afterSave
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
		$apiListener = new ApiListener($subject);
		$result = $apiListener->afterSave($event);
		$this->assertSame($subject->response, $result);
	}

/**
 * Tests afterSave method when some validation errors occurred
 *
 * @covers ApiListener::afterSave
 * @return void
 */
	public function testAfterSaveNotSuccess() {
		$subject = $this->getMock('CrudSubject');
		$subject->request = $this->getMock('CakeRequest', array('accepts', 'is'));
		$subject->controller = $this->getMock('Controller', array('set', 'render'), array($subject->request));
		$subject->response = $this->getMock('CakeResponse');

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
			->with('data', array('Thing' => $subject->model->validationErrors));

		$subject->controller->expects($this->never())->method('render');
		$event = new CakeEvent('Crud.afterSave', $subject);

		$subject->crud = new CrudComponent(new ComponentCollection());
		$apiListener = new ApiListener($subject);

		$this->assertNull($apiListener->afterSave($event));
	}

/**
 * Tests that afterDelete logic is not run if the call is not API
 *
 * @covers ApiListener::afterDelete
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
 * @covers ApiListener::afterDelete
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
 * @covers ApiListener::beforeRender
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
 * @covers ApiListener::beforeRender
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

/**
 * testChangingViewVarWillReflectSerialize
 *
 * @covers ApiListener::beforeRender
 * @return void
 */
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
 * @covers ApiListener::implementedEvents
 * @return void
 */
	public function testImplementeEvents() {
		$subject = $this->getMock('CrudSubject');
		$apiListener = new ApiListener($subject);
		$expected = array(
			'Crud.initialize' => array('callable' => 'initialize', 'priority' => 10),
			'Crud.startup' => array('callable' => 'startup', 'priority' => 5),
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
 * @covers ApiListener::setFlash
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
 * @covers ApiListener::setFlash
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
 *
 * @covers ApiListener::mapResources
 * @return void
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
 * @covers ApiListener::mapResources
 * @return void
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

/**
 * testViewClass
 *
 * Test that both set and get works
 *
 * @covers ApiListener::viewClass
 * @return void
 */
	public function testViewClass() {
		$apiListener = new ApiListener(new CrudSubject());

		$result = $apiListener->viewClass('json', 'Sample.ViewClass');
		$this->assertEqual($result, $apiListener, 'Setting a viewClass did not return the listener itself');

		$result = $apiListener->viewClass('json');
		$this->assertEqual($result, 'Sample.ViewClass', 'The changed viewClass was not returned');
	}

/**
 * testViewClassDefaults
 *
 * Test that the default viewClasses are as expected
 *
 * @covers ApiListener::viewClass
 * @return void
 */
	public function testViewClassDefaults() {
		$apiListener = new ApiListener(new CrudSubject());

		$result = $apiListener->config('viewClasses');
		$expected = array(
			'json' => 'Crud.CrudJson',
			'xml' => 'Crud.CrudXml'
		);
		$this->assertEqual($result, $expected, 'The default viewClasses setting has changed');
	}

/**
 * testInjectViewClasses
 *
 * @covers ApiListener::injectViewClasses
 * @return void
 */
	public function testInjectViewClasses() {
		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('foo')) // need to mock *something* to make Controller::__set work
			->disableOriginalConstructor()
			->getMock();

		$controller->RequestHandler = $this->getMock('RequestHandler', array('viewClassMap'));
		$controller->RequestHandler->expects($this->at(0))->method('viewClassMap')->with('json', 'Crud.CrudJson');
		$controller->RequestHandler->expects($this->at(1))->method('viewClassMap')->with('xml', 'Crud.CrudXml');

		$apiListener = $this->getMock('ApiListener', array('_controller'), array(new CrudSubject()));
		$apiListener->expects($this->once())->method('_controller')->will($this->returnValue($controller));
		$apiListener->injectViewClasses();
	}

}
