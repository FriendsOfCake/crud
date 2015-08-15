<?php
namespace Crud\Listener;

use Cake\Core\Plugin;
use Cake\Event\Event;
use RuntimeException;

class SearchListener extends BaseListener
{

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add your own listener callbacks
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Crud.beforePaginate' => ['callable' => 'beforePaginate']
        ];
    }

    /**
     * Inject search conditions to the qeury object.
     *
     * @param \Cake\Event\Event $event
     * @return void
     */
    public function beforePaginate(Event $event)
    {
        if (!Plugin::loaded('Search')) {
            throw new RuntimeException(
                'You need to load the Search plugin in order to use the SearchListener'
            );
        }

        $filterParams = $this->_table()->filterParams($this->_request()->query);
        $event->subject->query->find('search', $filterParams);
    }
}
