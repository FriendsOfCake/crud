<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Event\EventInterface;
use Cake\ORM\Table;
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
            'Crud.beforePaginate',
        ],
        'collection' => 'default',
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add your own listener callbacks
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Crud.beforeLookup' => ['callable' => 'injectSearch'],
            'Crud.beforePaginate' => ['callable' => 'injectSearch'],
        ];
    }

    /**
     * Inject search conditions into the query object.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function injectSearch(EventInterface $event): void
    {
        if (!in_array($event->getName(), $this->getConfig('enabled'))) {
            return;
        }

        $repository = $this->_table();
        if ($repository instanceof Table && !$repository->behaviors()->has('Search')) {
            throw new RuntimeException(sprintf(
                'Missing Search.Search behavior on %s',
                get_class($repository)
            ));
        }

        $filterParams = ['search' => $this->_request()->getQuery()];
        $filterParams['collection'] = $this->getConfig('collection');

        $event->getSubject()->query->find('search', $filterParams);
    }
}
