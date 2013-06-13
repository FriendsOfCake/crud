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
			'Crud.init' => array('callable' => 'init'),

			'Crud.beforePaginate' => array('callable' => 'beforePaginate', 'priority' => 100),
			'Crud.afterPaginate' => array('callable' => 'afterPaginate', 'priority' => 100),

			'Crud.recordNotFound' => array('callable' => 'recordNotFound', 'priority' => 100),
			'Crud.invalidId' => array('callable' => 'invalidId', 'priority' => 100),

			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 100),
			'Crud.beforeRedirect' => array('callable' => 'beforeRedirect', 'priority' => 100),

			'Crud.beforeSave' => array('callable' => 'beforeSave', 'priority' => 100),
			'Crud.afterSave' => array('callable' => 'afterSave', 'priority' => 100),

			'Crud.beforeFind' => array('callable' => 'beforeFind', 'priority' => 100),
			'Crud.afterFind' => array('callable' => 'afterFind', 'priority' => 100),

			'Crud.beforeDelete' => array('callable' => 'beforeDelete', 'priority' => 100),
			'Crud.afterDelete' => array('callable' => 'afterDelete', 'priority' => 100)
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
		parent::init($event);

		// Configure a few useful CakeRequest detectors
		$this->_setupDetectors();

		// Don't do anything if we aren't in an API request
		if (!$this->_request->is('api')) {
			return;
		}

		// Make sure that exceptions output in standard Crud format
		App::uses('CrudExceptionRenderer', 'Crud.Error');
		Configure::write('Exception.renderer', 'Crud.CrudExceptionRenderer');

		// Enforce a few REST rules before we do any heavy lifting
		$this->_enforceRequestType($event->subject->action, $event->subject->request);
	}

/**
 * afterSave callback
 *
 * @param CakeEvent $event
 * @return void|CakeResponse
 */
	public function afterSave(CakeEvent $event) {
		// Don't do anything if we aren't in an API request
		if (!$this->_request->is('api')) {
			return;
		}

		// Publish the success based on the CrudSubject's property
		$this->_controller->set('success', $event->subject->success);

		$model = $event->subject->model;
		// If we had an error in our save
		if (!$event->subject->success) {
			$event->subject->response->statusCode(400);
			// Set the data to be the validationErrors from the model
			$this->_controller->set('data', $model->validationErrors);
			return;
		}

		// Push the model ID back as response body if it's not set already
		if (empty($this->_controller->viewVars['data'])) {
			$this->_controller->set('data', array($model->alias => array($model->primaryKey => $event->subject->id)));
		}

		// Render the view
		$this->beforeRender($event);
		$response = $this->_controller->render();

		// REST says newly created objects should get a "201 Created" response code back
		if ($event->subject->created) {
			$response->statusCode(201);
		} else {
			$response->statusCode(301);
		}

		// Send a redirect header for the 'view' action
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
		// Don't do anything if we aren't in an API request
		if (!$this->_request->is('api')) {
			return;
		}

		$event->stopPropagation();

		$this->_controller->set('success', $event->subject->success);
		$this->_controller->set('data', null);

		return $this->_controller->render();
	}

/**
 * recordNotFound
 *
 * Always send a HTTP 404 response if something can't be found
 *
 * @param CakeEvent $event
 * @return void
 * @throws NotFoundException If record not found
 */
	public function recordNotFound(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		throw new NotFoundException();
	}

/**
 * invalidId
 *
 * If the id is invalid, simply send a HTTP 400 response
 *
 * @param CakeEvent $event
 * @return void
 * @throws BadRequestException If invalid id provided
 */
	public function invalidId(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		throw new BadRequestException('Invalid id');
	}

/**
 * Selects an specific Crud view class to render the output
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		// Copy the _serialize configuration from the CrudAction config
		$action = $event->subject->crud->action();
		$serialize = $action->config('serialize');

		$serialize[] = 'success';
		if (method_exists($action, 'viewVar')) {
			$serialize[$action->viewVar()] = 'data';
		} else {
			$serialize[] = 'data';
		}
		$this->_controller->set('_serialize', $serialize);

		// Make sure to use Cruds own View renderer for json and xml
		// @TODO: make the viewClassMap configurable
		$this->_controller->RequestHandler->viewClassMap('json', 'Crud.CrudJson');
		$this->_controller->RequestHandler->viewClassMap('xml', 'Crud.CrudXml');
		$this->_controller->RequestHandler->renderAs($this->_controller, $this->_controller->RequestHandler->ext);
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
		$this->_request->addDetector('json', array('callback' => function(CakeRequest $request) {
			if (isset($request->params['ext']) && $request->params['ext'] === 'json') {
				return true;
			}

			return $request->accepts('application/json');
		}));

		$this->_request->addDetector('xml', array('callback' => function(CakeRequest $request) {
			if (isset($request->params['ext']) && $request->params['ext'] === 'xml') {
				return true;
			}

			return $request->accepts('text/xml');
		}));

		$this->_request->addDetector('api', array('callback' => function(CakeRequest $request) {
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

}
