<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

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
			'Crud.init'	=> array('callable' => 'init'),

			'Crud.beforePaginate'	=> array('callable' => 'beforePaginate', 'priority' => 100),
			'Crud.afterPaginate'	=> array('callable' => 'afterPaginate', 'priority' => 100),

			'Crud.recordNotFound'	=> array('callable' => 'recordNotFound', 'priority' => 100),
			'Crud.invalidId'		=> array('callable' => 'invalidId', 'priority' => 100),

			'Crud.beforeRender'		=> array('callable' => 'beforeRender', 'priority' => 100),
			'Crud.beforeRedirect'	=> array('callable' => 'beforeRedirect', 'priority' => 100),

			'Crud.beforeSave'		=> array('callable' => 'beforeSave', 'priority' => 100),
			'Crud.afterSave'		=> array('callable' => 'afterSave', 'priority' => 100),

			'Crud.beforeFind'		=> array('callable' => 'beforeFind', 'priority' => 100),
			'Crud.afterFind'		=> array('callable' => 'afterFind', 'priority' => 100),

			'Crud.beforeDelete'		=> array('callable' => 'beforeDelete', 'priority' => 100),
			'Crud.afterDelete'		=> array('callable' => 'afterDelete', 'priority' => 100),
		);
	}

	public function init(CakeEvent $event) {
		parent::init($event);

		$this->_setupDetectors();

		App::uses('CrudExceptionRenderer', 'Crud.Error');
		Configure::write('Exception.renderer', 'Crud.CrudExceptionRenderer');

		if (!$this->_request->is('api')) {
			return;
		}

		$this->_enforceRequestType($event->subject->action);
	}

	public function afterSave(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		if ($event->subject->success) {
			$model = $event->subject->model;
			$controller = $event->subject->controller;

			if (empty($controller->viewVars['data'])) {
				$controller->set('data', array($model->alias => array($model->primaryKey => $event->subject->model->id)));
			}

			$response = $event->subject->controller->render();
			$response->statusCode(201);
			$response->header('Location', \Router::url(array('action' => 'view', $event->subject->id), true));
		} else {
			$response = $event->subject->controller->render();
			$response->statusCode(400);
		}

		$event->stopPropagation();
		return $response;
	}

	public function afterDelete(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		$event->subject->controller->set('success', $event->subject->success);
		$event->stopPropagation();
		return $event->subject->controller->render();
	}

	public function recordNotFound(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		throw new NotFoundException();
	}

	public function invalidId(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		throw new NotFoundException('Invalid id specified');
	}

	public function beforeRender(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		$this->_controller->set('_serialize', $event->subject->crud->getAction()->config('serialize'));
		$this->_controller->RequestHandler->viewClassMap('json', 'Crud.CrudJson');
		$this->_controller->RequestHandler->viewClassMap('xml', 'Crud.CrudXml');
		$this->_controller->RequestHandler->renderAs($this->_controller, $this->_controller->RequestHandler->ext);
	}

	/**
	* Is the current controller an Error controller?
	*
	* @return boolean
	*/
	public function hasError(Controller $controller) {
		return get_class($controller) == 'CakeErrorController';
	}

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

			return $request->accepts('xml');
		}));

		$this->_request->addDetector('api', array('callback' => function(CakeRequest $request) {
			return $request->is('json') || $request->is('xml');
		}));

	}

	protected function _enforceRequestType($action) {
		switch ($action) {
			case 'index':
			case 'admin_index':
				if (!$event->subject->request->is('get')) {
					throw new \MethodNotAllowedException();
				}
				break;
			case 'add':
			case 'admin_add':
				if (!$event->subject->request->is('post')) {
					throw new \MethodNotAllowedException();
				}
				break;
			case 'edit':
			case 'admin_edit':
				if (!$event->subject->request->is('put')) {
					throw new \MethodNotAllowedException();
				}
				break;
			case 'delete':
			case 'admin_delete':
				if (!$event->subject->request->is('delete')) {
					throw new \MethodNotAllowedException();
				}
				break;
		}
	}

}
