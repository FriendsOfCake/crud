<?php

App::uses('DebugPanel', 'DebugKit.Lib');

/**
 * Crud debug panel in DebugKit
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
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

		$listenerConfig = array();
		foreach ($controller->Crud->config('listeners') as $listener => $value) {
			$listenerConfig[$listener] = $controller->Crud->listener($listener)->config();
		}

		$controller->set('CRUD_config', $controller->Crud->config());
		$controller->set('CRUD_listener_config', $listenerConfig);
	}

}
