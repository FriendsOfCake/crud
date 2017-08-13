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
        ],
        'collection' => 'default'
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
        if (!in_array($event->name, $this->config('enabled'))) {
            return;
        }

        $table = $this->_table();
        if (!$table->behaviors()->has('Search')) {
            throw new RuntimeException(sprintf(
                'Missing Search.Search behavior on %s',
                get_class($table)
            ));
        }

        if ($table->behaviors()->hasMethod('filterParams')) {
            $filterParams = $table->filterParams($this->_request()->query);
        } else {
            $filterParams = ['search' => $this->_request()->query];
        }

        $filterParams['collection'] = $this->config('collection');
        $event->subject->query->find('search', $filterParams);
    }
}
