<?php

namespace Crud\Listener;

use Cake\Event\Event;

/**
 * When loaded Crud API Pagination Listener will include
 * pagination information in the response
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiPagination extends Base {

/**
 * Returns a list of all events that will fire in the controller during its lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 10 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		return [
			'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75]
		];
	}

/**
 * Appends the pagination information to the JSON or XML output
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(Event $event) {
		$request = $this->_request();
		if (!$request->is('api')) {
			return;
		}

		$_pagination = $request->paging;
		$modelClass = $this->_controller()->modelClass;
		if (empty($_pagination) || !array_key_exists($modelClass, $_pagination)) {
			return;
		}

		$_pagination = $_pagination[$modelClass];

		$pagination = [
			'page_count' => $_pagination['pageCount'],
			'current_page' => $_pagination['page'],
			'has_next_page' => $_pagination['nextPage'],
			'has_prev_page' => $_pagination['prevPage'],
			'count' => $_pagination['count'],
			'limit' => $_pagination['limit']
		];

		$this->_action()->config('serialize.pagination', 'pagination');
		$this->_controller()->set('pagination', $pagination);
	}
}
