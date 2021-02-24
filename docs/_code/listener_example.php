<?php
namespace Crud\Listener;

class Example extends \Crud\Listener\BaseListener
{

    /**
     * Returns a list of all events that will fire in the lister during the Crud life-cycle.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            'Crud.beforeRender' => ['callable' => 'beforeRender']
        ];
    }

    /**
     * Executed when Crud.beforeRender is emitted.
     *
     * @param \Cake\Event\EventInterface $event Event instance
     *
     * @return void
     */
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        $this->_response()->header('X-Powered-By', 'CRUD 4.0');
    }

}
