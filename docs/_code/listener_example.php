<?php
namespace Crud\Listener;

class Example extends \Crud\Listener\Base {

/**
 * Returns a list of all events that will fire in the lister during the
 * CRUD life-cycle.
 *
 * @return array
 */
  public function implementedEvents() {
    return [
      'Crud.beforeRender' => ['callable' => 'beforeRender']
    ];
  }

/**
 * Executed when Crud.beforeRender is emitted
 *
 * @param \Cake\Event\Évent $event
 * @return void
 */
  public function beforeRender(Cake\Event\Event $event) {
    $this->_response()->header('X-Powered-By', 'CRUD 4.0');
  }

}
