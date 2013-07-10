<?php

App::uses ('DebugPanel', 'DebugKit.Lib');

/**
 * Crud debug panel in DebugKit
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudPanel extends DebugPanel {

/**
 * Declare we are a plugin
 *
 * @var string
 */
	public $plugin = 'Crud';

/**
 * beforeRender callback
 *
 * @param Controller $controller
 * @return void
 */
	public function beforeRender(Controller $controller) {
		if ($controller->Crud->isActionMapped()) {
			$Action = $controller->Crud->action();
			$controller->set('CRUD_action_config', $Action->config());
		}

		$listener_config = array();
		foreach ($controller->Crud->config('listeners') as $listener => $value) {
			$listener_config[$listener] = $controller->Crud->listener($listener)->config();
		}

		$controller->set('CRUD_config', $controller->Crud->config());
		$controller->set('CRUD_listener_config', $listener_config);
	}

}
