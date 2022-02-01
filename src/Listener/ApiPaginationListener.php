<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Event\EventInterface;

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
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        if (!$this->_checkRequestType('api')) {
            return [];
        }

        return [
            'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75],
        ];
    }

    /**
     * Appends the pagination information to the JSON or XML output
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        $paging = $this->_request()->getAttribute('paging');
        if (empty($paging)) {
            return;
        }

        $pagination = current($paging);
        if (empty($pagination)) {
            return;
        }

        $paginationResponse = [
            'page_count' => $pagination['pageCount'],
            'current_page' => $pagination['page'],
            'has_next_page' => $pagination['nextPage'],
            'has_prev_page' => $pagination['prevPage'],
            'count' => $pagination['count'],
            'limit' => $pagination['limit'],
        ];

        $this->_controller()->set('pagination', $paginationResponse);
        $this->_action()->setConfig('serialize.pagination', 'pagination');
    }
}
