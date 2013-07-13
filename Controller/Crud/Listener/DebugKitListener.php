<?php

App::uses('CakeEvent', 'Event');
App::uses('DebugTimer', 'DebugKit.Lib');
App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * Implements timings for DebugKit / Crud
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class DebugKitListener extends CrudListener {

/**
 * List of events implemented by this class
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.init' => array('callable' => 'init', 'priority' => 1),
			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 5000),

			'Crud.beforePaginate' => array('callable' => 'beforePaginate', 'priority' => 1),
			'Crud.afterPaginate' => array('callable' => 'afterPaginate', 'priority' => 5000),

			'Crud.beforeSave' => array('callable' => 'beforeSave', 'priority' => 1),
			'Crud.afterSave' => array('callable' => 'afterSave', 'priority' => 5000),

			'Crud.beforeFind' => array('callable' => 'beforeFind', 'priority' => 1),
			'Crud.afterFind' => array('callable' => 'afterFind', 'priority' => 5000),

			'Crud.beforeDelete' => array('callable' => 'beforeDelete', 'priority' => 1),
			'Crud.afterDelete' => array('callable' => 'afterDelete', 'priority' => 5000),
		);
	}

	public function init(CakeEvent $event) {
		DebugTimer::start('Event: Crud.init');
	}

	public function beforeRender(CakeEvent $event) {
		DebugTimer::stop('Event: Crud.init');
	}

	public function beforePaginate(CakeEvent $event) {
		DebugTimer::start('Event: Crud.Paginate');
	}

	public function afterPaginate(CakeEvent $event) {
		DebugTimer::stop('Event: Crud.Paginate');
	}

	public function beforeSave(CakeEvent $event) {
		DebugTimer::start('Event: Crud.Save');
	}

	public function afterSave(CakeEvent $event) {
		DebugTimer::stop('Event: Crud.Save');
	}

	public function beforeFind(CakeEvent $event) {
		DebugTimer::start('Event: Crud.Find');
	}

	public function afterFind(CakeEvent $event) {
		DebugTimer::stop('Event: Crud.Find');
	}

	public function beforeDelete(CakeEvent $event) {
		DebugTimer::start('Event: Crud.Delete');
	}

	public function afterDelete(CakeEvent $event) {
		DebugTimer::stop('Event: Crud.Delete');
	}

}
