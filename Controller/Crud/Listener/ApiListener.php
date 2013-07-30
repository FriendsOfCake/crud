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
 * Returns a list of all events that will fire in the controller during it's lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 10 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.init' => array('callable' => 'init', 'priority' => 10),
			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 100),
			'Crud.afterSave' => array('callable' => 'afterSave', 'priority' => 100),
			'Crud.afterDelete' => array('callable' => 'afterDelete', 'priority' => 100),
			'Crud.setFlash' => array('callable' => 'setFlash', 'priority' => 100)
		);
	}

/**
 * Init
 *
 * Called when the listener is started
 *
 * @param CakeEvent $event
 * @return void
 */
	public function init(CakeEvent $event) {
		// Configure a few useful CakeRequest detectors
		$this->_setupDetectors();

		// Don't do anything if we aren't in an API request
		if (!$this->_request()->is('api')) {
			return;
		}

		// Make sure that exceptions output in standard Crud format
		App::uses('CrudExceptionRenderer', 'Crud.Error');
		Configure::write('Exception.renderer', 'Crud.CrudExceptionRenderer');

		// Enforce a few REST rules before we do any heavy lifting
		$this->_enforceRequestType($event->subject->action, $this->_request());
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

		$model = $this->_model();
		$controller = $this->_controller();
		$controller->set('success', $event->subject->success);

		if (!$event->subject->success) {
			$event->subject->response->statusCode(400);
			$controller->set('data', $model->validationErrors);
			return;
		}

		if (empty($controller->viewVars['data'])) {
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

		$controller = $this->_controller();
		$controller->set('_serialize', $serialize);

		// @TODO: make the viewClassMap configurable
		$controller->RequestHandler->viewClassMap('json', 'Crud.CrudJson');
		$controller->RequestHandler->viewClassMap('xml', 'Crud.CrudXml');
		$controller->RequestHandler->renderAs($this->_controller, $controller->RequestHandler->ext);
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
 * Enforce REST HTTP request types
 *
 * "index" actions should only be accessible through HTTP GET
 * "view" actions should only be accessible through HTTP GET
 * "add" actions should only be accessible through HTTP POST
 * "edit" actions should only be accessible through HTTP PUT
 * "delete" actions should only be accessible through HTTP DELETE
 *
 * Unknown actions will be ignored
 *
 * @TODO make this configurable on both HTTP verbs and actions
 * @param string $action
 * @param CakeRequest $request
 * @return void
 * @throws MethodNotAllowedException If method not allowed
 */
	protected function _enforceRequestType($action, CakeRequest $request) {
		switch ($action) {
			case 'index':
			case 'admin_index':
			case 'view':
			case 'admin_view':
				if (!$request->is('get')) {
					throw new MethodNotAllowedException();
				}
				break;

			case 'add':
			case 'admin_add':
				if (!$request->is('post')) {
					throw new MethodNotAllowedException();
				}
				break;

			case 'edit':
			case 'admin_edit':
				if (!$request->is('put')) {
					throw new MethodNotAllowedException();
				}
				break;

			case 'delete':
			case 'admin_delete':
				if (!$request->is('delete')) {
					throw new MethodNotAllowedException();
				}
				break;
		}
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
	public static function mapResources($plugin = null){
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
