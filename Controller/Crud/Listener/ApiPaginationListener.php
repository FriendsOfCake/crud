<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * When loaded Crud API will include query logs in the response
 *
 * Very much like the DebugKit version, the SQL log will only be appended
 * if the following conditions is true:
 *  1) The request must be 'api' (.json/.xml)
 *  2) The debug level must be 2 or above
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ApiPaginationListener extends CrudListener {

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
			'Crud.beforeRender' => array('callable' => 'beforeRender')
		);
	}

/**
 * Appends the query log to the JSON or XML output
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		if (!$this->_request->is('api')) {
			return;
		}

		$this->_controller->helpers[] = 'Crud.ApiPagination';
		$this->_crud->action()->config('serialize.pagination', 'pagination');
	}
}
