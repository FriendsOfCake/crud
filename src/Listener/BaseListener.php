<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Event\EventListenerInterface;
use Crud\Core\BaseObject;

/**
 * The Base Crud Listener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 *
 * @codeCoverageIgnore
 */
abstract class BaseListener extends BaseObject implements EventListenerInterface
{
    /**
     * Returns a list of all events that will fire in the controller during its life cycle.
     * You can override this function to add you own listener callbacks.
     *
     * - initialize: Called at the same time as CrudComponent::initialize()
     * - startup: Called at the same time as CrudComponent::startup()
     * - beforeHandle : Called before CrudAction is executed
     * - recordNotFound : Called if a find() did not return any records
     * - beforePaginate : Called right before any paginate() method
     * - afterPaginate : Called right after any paginate() method
     * - invalidId : Called if the ID format validation failed
     * - setFlash : Called before any FlashComponent::set()
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        $eventMap = [
            'Crud.beforeFilter' => 'beforeFilter',
            'Crud.startup' => 'startup',

            'Crud.beforeHandle' => 'beforeHandle',

            'Crud.beforePaginate' => 'beforePaginate',
            'Crud.afterPaginate' => 'afterPaginate',

            'Crud.recordNotFound' => 'recordNotFound',
            'Crud.invalidId' => 'invalidId',
            'Crud.setFlash' => 'setFlash',

            'Crud.beforeRender' => 'beforeRender',
            'Crud.beforeRedirect' => 'beforeRedirect',

            'Crud.beforeSave' => 'beforeSave',
            'Crud.afterSave' => 'afterSave',

            'Crud.beforeFind' => 'beforeFind',
            'Crud.afterFind' => 'afterFind',

            'Crud.beforeDelete' => 'beforeDelete',
            'Crud.afterDelete' => 'afterDelete',
        ];

        $events = [];
        foreach ($eventMap as $event => $method) {
            if (method_exists($this, $method)) {
                $events[$event] = $method;
            }
        }

        return $events;
    }

    /**
     * Return the human name of the model
     *
     * By default it uses Inflector::humanize, but can be changed
     * using the "name" configuration property
     *
     * @param string|null $value Value
     * @return string|$this
     */
    public function resourceName(?string $value = null)
    {
        if ($value === null) {
            $this->_action()->resourceName();

            return $this;
        }

        /** @var string */
        return $this->_action()->resourceName($value);
    }
}
