<?php
namespace Crud\Listener;

use Cake\Core\Plugin;
use Cake\Event\Event;
use RuntimeException;

class SearchListener extends BaseListener
{

    /**
     * Settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => [
            'Crud.beforeLookup',
            'Crud.beforePaginate'
        ]
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add your own listener callbacks
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Crud.beforeLookup' => ['callable' => 'injectSearch'],
            'Crud.beforePaginate' => ['callable' => 'injectSearch']
        ];
    }

    /**
     * Inject search conditions to the qeury object.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function injectSearch(Event $event)
    {
        if (!Plugin::loaded('Search')) {
            throw new RuntimeException(
                'You need to load the Search plugin in order to use the SearchListener.'
            );
        }

        if (!in_array($event->name, $this->config('enabled'))) {
            return;
        }

        $table = $this->_table();
        if (!$table->behaviors()->hasMethod('filterParams')) {
            throw new RuntimeException(sprintf(
                'Missing Search.Search behavior on %s',
                get_class($table)
            ));
        }

        $filterParams = $table->filterParams($this->_request()->query);
        $event->subject->query->find('search', $filterParams);
    }
}
