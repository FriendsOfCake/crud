<?php

App::uses ('DebugPanel', 'DebugKit.Lib');

class CrudPanel extends DebugPanel {

	public $plugin = 'Crud';

	public function startup(Controller $controller) {
		if (!$controller->Crud->isActionMapped()) {
			return;
		}

	}

	public function beforeRender(Controller $controller) {
		if (!$controller->Crud->isActionMapped()) {
			return;
		}

		$Action = $controller->Crud->action();
		$controller->set('CRUD_config', $controller->Crud->config());
		$controller->set('CRUD_action_config', $Action->config());

		$listener_config = array();
		foreach ($controller->Crud->config('listeners') as $listener => $value) {
			$listener_config[$listener] = $controller->Crud->listener($listener)->config();
		}

		$controller->set('CRUD_listener_config', $listener_config);
	}

}
