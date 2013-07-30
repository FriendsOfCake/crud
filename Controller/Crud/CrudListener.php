<?php

App::uses('CrudBaseObject', 'Crud.Controller/Crud');
App::uses('CakeEventListener', 'Event');
App::uses('Hash', 'Utility');

/**
 * The Base Crud Listener
 *
 * All callbacks are defined here for good measure
 *
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
abstract class CrudListener extends CrudBaseObject implements CakeEventListener {

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * - init : Called before any other method in the decorator.
 *     Just set the arguments as instance properties for easier access later
 * - recordNotFound : Called if a find() did not return any records
 * - beforePaginate : Called right before any paginate() method
 * - afterPaginate : Called right after any paginate() method
 * - invalidId : Called if the ID format validation failed
 * - setFlash : Called before any CakeSession::setFlash
 *     Subject contains the following keys you can modify:
 * 	     - message
 * 	     - element = 'default',
 * 	     - params = array()
 * 	     - key = 'flash'
 *
 * @codeCoverageIgnore
 * @return array
 */
	public function implementedEvents() {
		$eventMap = array(
			'Crud.init'	=> 'init',

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
			'Crud.afterDelete' => 'afterDelete'
		);

		$events = array();
		foreach ($eventMap as $event => $method) {
			if (method_exists($this, $method)) {
				$event[$event] = $method;
			}
		}

		return $events;
	}

}
