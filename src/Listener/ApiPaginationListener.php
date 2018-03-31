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
class ApiPaginationListener extends BaseListener
{

    /**
     * Returns a list of all events that will fire in the controller during its life-cycle.
     * You can override this function to add you own listener callbacks
     *
     * We attach at priority 10 so normal bound events can run before us
     *
     * @return array|null
     */
    public function implementedEvents()
    {
        if (!$this->_checkRequestType('api')) {
            return null;
        }

        return [
            'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75]
        ];
    }

    /**
     * Appends the pagination information to the JSON or XML output
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $request = $this->_request();

        if (empty($request->getParam('paging'))) {
            return;
        }

        $controller = $this->_controller();
        list(, $modelClass) = pluginSplit($controller->modelClass);

        if (!array_key_exists($modelClass, $request->getParam('paging'))) {
            return;
        }

        $pagination = $request->getParam('paging')[$modelClass];
        if (empty($pagination)) {
            return;
        }

        $paginationResponse = [
            'page_count' => $pagination['pageCount'],
            'current_page' => $pagination['page'],
            'has_next_page' => $pagination['nextPage'],
            'has_prev_page' => $pagination['prevPage'],
            'count' => $pagination['count'],
            'limit' => $pagination['limit']
        ];

        $controller->set('pagination', $paginationResponse);
        $this->_action()->setConfig('serialize.pagination', 'pagination');
    }
}
