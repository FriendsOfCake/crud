<?php

App::uses('CakeEventListener', 'Event');

/**
 * The Base Crud Listener
 *
 * All callbacks are defined here for good measure
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 * @abstract
 */
abstract class CrudListener extends Object implements CakeEventListener {

/**
 * Returns a list of all events that will fire in the controller during it's life cycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.init'	=> array('callable' => 'init'),

			'Crud.beforePaginate' => array('callable' => 'beforePaginate'),
			'Crud.afterPaginate' => array('callable' => 'afterPaginate'),

			'Crud.recordNotFound' => array('callable' => 'recordNotFound'),
			'Crud.invalidId' => array('callable' => 'invalidId'),
			'Crud.setFlash' => array('callable' => 'setFlash'),

			'Crud.beforeRender' => array('callable' => 'beforeRender'),
			'Crud.beforeRedirect' => array('callable' => 'beforeRedirect'),

			'Crud.beforeSave' => array('callable' => 'beforeSave'),
			'Crud.afterSave' => array('callable' => 'afterSave'),

			'Crud.beforeFind' => array('callable' => 'beforeFind'),
			'Crud.afterFind' => array('callable' => 'afterFind'),

			'Crud.beforeDelete' => array('callable' => 'beforeDelete'),
			'Crud.afterDelete' => array('callable' => 'afterDelete'),

			'Crud.beforeListRelated' => array('callable' => 'beforeListRelated'),
			'Crud.afterListRelated'	=> array('callable' => 'afterListRelated'),
		);
	}

/**
 * Initialize method
 *
 * Called before any other method in the decorator
 *
 * Just set the arguments as instance properties for easier access later
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function init(CakeEvent $event) {

	}

/**
 * Called before a record is saved in add or edit actions
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeSave(CakeEvent $event) {

	}

/**
 * Called before any CRUD redirection
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeRedirect(CakeEvent $event) {

	}

/**
 * Called before any find() on the model
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeFind(CakeEvent $event) {

	}

/**
 * After find callback
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterFind(CakeEvent $event) {

	}

/**
 * Called after any save() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterSave(CakeEvent $event) {

	}

/**
 * Called before cake's own render()
 *
 * CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeRender(CakeEvent $event) {

	}

/**
 * Called before any delete() action
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeDelete(CakeEvent $event) {

	}

/**
 * Called after any delete() action
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterDelete(CakeEvent $event) {

	}

/**
 * Called before related records list for a model is fetched.
 * `$event->subject` will contain the following properties that can be modified:
 *
 * - query: An array with options for find('list')
 * - model: Model instance, the model to be used for fiding the list or records
 *
 *  @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforeListRelated(CakeEvent $event) {

	}

/**
 * Called after related records list for a model is fetched
 * `$event->subject` will contain the following properties that can be modified:
 *
 * - items: result from calling find('list')
 * - viewVar: Variable name to be set on the view with items as value
 * - model: Model instance, the model to be used for fiding the list or records
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterListRelated(CakeEvent $event) {

	}

/**
 * Called if a find() did not return any records
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function recordNotFound(CakeEvent $event) {

	}

/**
 * Called right before any paginate() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function beforePaginate(CakeEvent $event) {

	}

/**
 * Called right after any paginate() method
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function afterPaginate(CakeEvent $event) {

	}

/**
 * Called if the ID format validation failed
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function invalidId(CakeEvent $event) {

	}

/**
 * Called before any CakeSession::setFlash
 *
 * Subject contains the following keys you can modify:
 * 	- message
 * 	- element = 'default',
 * 	- params = array()
 * 	- key = 'flash'
 *
 * @param CakeEvent $event The CakePHP CakeEvent object.
 * @return void
 */
	public function setFlash(CakeEvent $event) {

	}

}
