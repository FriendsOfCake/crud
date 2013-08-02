<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * Enabled Crud to respond in a computer readable format like JSON or XML
 *
 * It tries to enforce some REST principles and keep some string conventions in the output format
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ApiListener extends CrudListener {

/**
 * Default configuration
 *
 * @var array
 */
	protected $_settings = array(
		'viewClasses' => array(
			'json' => 'Crud.CrudJson',
			'xml' => 'Crud.CrudXml'
		)
	);

/**
 * Returns a list of all events that will fire in the controller during it's lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 10 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.startup' => array('callable' => 'startup', 'priority' => 5),
			'Crud.initialize' => array('callable' => 'initialize', 'priority' => 10),
			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 100),
			'Crud.afterSave' => array('callable' => 'afterSave', 'priority' => 100),
			'Crud.afterDelete' => array('callable' => 'afterDelete', 'priority' => 100),
			'Crud.setFlash' => array('callable' => 'setFlash', 'priority' => 100)
		);
	}

/**
 * Called when all listeners has been loaded,
 * and before the crud action is actually executed
 *
 * @param CakeEvent $event
 * @return void
 */
	public function startup(CakeEvent $event) {
		$this->_setupDetectors();
	}

/**
 * initialize
 *
 * Called before the crud action is executed
 *
 * @param CakeEvent $event
 * @return void
 */
	public function initialize(CakeEvent $event) {
		parent::initialize($event);

		if (!$this->_request()->is('api')) {
			return;
		}

		$this->registerExceptionHandler();
	}

	public function registerExceptionHandler() {
		App::uses('CrudExceptionRenderer', 'Crud.Error');
		Configure::write('Exception.renderer', 'Crud.CrudExceptionRenderer');
	}

/**
 * afterSave callback
 *
 * @param CakeEvent $event
 * @return void|CakeResponse
 */
	public function afterSave(CakeEvent $event) {
		if (!$this->_request()->is('api')) {
			return;
		}

		$controller = $this->_controller();
		$controller->set('success', $event->subject->success);

		if (!$event->subject->success) {
			$event->subject->response->statusCode(400);
			$crud = $this->_crud();
			$controller->set('data', $crud->validationErrors());
			return;
		}

		if (empty($controller->viewVars['data'])) {
			$model = $this->_model();
			$controller->set('data', array($model->alias => array($model->primaryKey => $event->subject->id)));
		}

		$this->beforeRender($event);
		$response = $controller->render();

		if ($event->subject->created) {
			$response->statusCode(201);
		} else {
			$response->statusCode(301);
		}

		$response->header('Location', Router::url(array('action' => 'view', $event->subject->id), true));
		return $response;
	}

/**
 * afterDelete
 *
 * @param CakeEvent $event
 * @return void
 */
	public function afterDelete(CakeEvent $event) {
		if (!$this->_request()->is('api')) {
			return;
		}

		$event->stopPropagation();

		$this->beforeRender($event);

		$controller = $this->_controller();
		$controller->set('success', $event->subject->success);
		$controller->set('data', null);

		return $controller->render();
	}

/**
 * Selects an specific Crud view class to render the output
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		if (!$this->_request()->is('api')) {
			return;
		}

		$action = $this->_action();

		$serialize = array();
		$serialize[] = 'success';
		if (method_exists($action, 'viewVar')) {
			$serialize['data'] = $action->viewVar();
		} else {
			$serialize[] = 'data';
		}

		$serialize = array_merge($serialize, $action->config('serialize'));

		$this->injectViewClasses();

		$controller = $this->_controller();
		$controller->set('_serialize', $serialize);
		$controller->RequestHandler->renderAs($controller, $controller->RequestHandler->ext);
	}

/**
 * Inject view classes into RequestHandler
 *
 * @see http://book.cakephp.org/2.0/en/core-libraries/components/request-handling.html#using-custom-viewclasses
 * @return void
 */
	public function injectViewClasses() {
		$controller = $this->_controller();
		foreach ($this->config('viewClasses') as $type => $class) {
			$controller->RequestHandler->viewClassMap($type, $class);
		}
	}

/**
 * Get or set a viewClass
 *
 * `$type` could be `json`, `xml` or any other valid type
 * 		defined by the `RequestHandler`
 *
 * `$class` could be any View class capable of handling
 * 		the response format for the `$type`. Normal
 * 		CakePHP plugin "dot" notation is supported
 *
 * @see http://book.cakephp.org/2.0/en/core-libraries/components/request-handling.html#using-custom-viewclasses
 * @param string $type
 * @param string $class
 * @return mixed
 */
	public function viewClass($type, $class = null) {
		if (is_null($class)) {
			return $this->config('viewClasses.' . $type);
		}

		return $this->config('viewClasses.' . $type, $class);
	}

/**
 * setFlash
 *
 * An api request doesn't need flash messages - so stop them being processed
 *
 * @param CakeEvent $event
 */
	public function setFlash(CakeEvent $event) {
		if (!$this->_request()->is('api')) {
			return;
		}

		$event->stopPropagation();
	}

/**
 * Setup detectors for JSON and XML
 *
 * Both detects on two signals:
 *  1) The extension in the request (e.g. /users/index.json)
 *  2) The accepts header from the client
 *
 * There is a combined request detector for both 'json' and 'xml' called
 * 'api'
 *
 * @return void
 */
	protected function _setupDetectors() {
		$request = $this->_request();

		$request->addDetector('json', array('callback' => function(CakeRequest $request) {
			if (isset($request->params['ext']) && $request->params['ext'] === 'json') {
				return true;
			}

			return $request->accepts('application/json');
		}));

		$request->addDetector('xml', array('callback' => function(CakeRequest $request) {
			if (isset($request->params['ext']) && $request->params['ext'] === 'xml') {
				return true;
			}

			return $request->accepts('text/xml');
		}));

		$request->addDetector('api', array('callback' => function(CakeRequest $request) {
			return $request->is('json') || $request->is('xml');
		}));
	}

/**
 * Automatically create REST resource routes for all controllers found in your main
 * application or in a specific plugin to provide access to your resources
 * using /controller/id.json instead of the default /controller/view/id.json.
 *
 * If called with no arguments, all controllers in the main application will be mapped.
 * If called with a valid plugin name all controllers in that plugin will be mapped.
 * If combined both controllers from the application and the plugin(s) will be mapped.
 *
 * This function needs to be called from your application's app/Config/routes.php:
 *
 * ```
 *     App::uses('ApiListener', 'Crud.Controller/Crud/Listener');
 *
 *     ApiListener::mapResources();
 *     ApiListener::mapResources('DebugKit');
 *     Router::setExtensions(array('json', 'xml'));
 *     Router::parseExtensions();
 * ```
 *
 * @static
 * @param string $plugin
 * @return void
 */
	public static function mapResources($plugin = null) {
		$key = 'Controller';
		if ($plugin) {
			$key = $plugin . '.Controller';
		}

		$controllers = array();
		foreach (App::objects($key) as $controller) {
			if ($controller !== $plugin . 'AppController') {
				if ($plugin) {
					$controller = $plugin . '.' . $controller;
				}

				array_push($controllers, str_replace('Controller', '', $controller));
			}
		}

		Router::mapResources($controllers);
	}
}
