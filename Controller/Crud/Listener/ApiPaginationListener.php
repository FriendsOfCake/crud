<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * When loaded Crud API Pagination Listener will include
 * pagination information in the response
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

		$_pagination = $this->_controller->request->paging;
		$_pagination = $_pagination[$event->subject->modelClass];

		$pagination = array(
			'page_count' => $_pagination['pageCount'],
			'current_page' => $_pagination['page'],
			'page_count' => $_pagination['count'],
			'has_next_page' => $_pagination['nextPage'],
			'has_prev_page' => $_pagination['prevPage'],
			'limit' => $_pagination['limit']
		);

		$this->_crud->action()->config('serialize.pagination', 'pagination');
		$this->_controller->set('pagination', $pagination);
	}
}
