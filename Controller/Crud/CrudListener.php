<?php

App::uses('CrudBaseObject', 'Crud.Controller/Crud');

/**
 * The Base Crud Listener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class CrudListener extends CrudBaseObject {

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * - beforeHandle : Called before CrudAction is executed
 * - startup: Called after the Controller::beforeFilter() and before the Crud action is invoked
 * - recordNotFound : Called if a find() did not return any records
 * - beforePaginate : Called right before any paginate() method
 * - afterPaginate : Called right after any paginate() method
 * - invalidId : Called if the ID format validation failed
 * - setFlash : Called before any CakeSession::setFlash
 *
 * @codeCoverageIgnore
 * @return array
 */
	public function implementedEvents() {
		$eventMap = array(
			'Crud.beforeHandle' => 'beforeHandle',
			'Crud.startup' => 'startup',

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

			'Crud.beforeCancel' => 'beforeCancel',
		);

		$events = array();
		foreach ($eventMap as $event => $method) {
			if (method_exists($this, $method)) {
				$events[$event] = $method;
			}
		}

		return $events;
	}

}
