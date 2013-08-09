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
		$panelData['component'] = $controller->Crud->config();

		if ($controller->Crud->isActionMapped()) {
			$Action = $controller->Crud->action();
			$panelData['action'] = $Action->config();
		}

		$eventManager = $controller->getEventManager();
		$eventLog = $controller->Crud->eventLog();
		$events = array();
		foreach ($eventLog as $event) {
			$suffix = '';
			list($name, $data) = $event;

			while (isset($events[$name . $suffix])) {
				if (!$suffix) {
					$suffix = ' #2';
				} else {
					$suffix = ' #' . int($suffix);
				}
			}

			$callbacks = $this->_getCallbacks($eventManager, $name);
			$events[$name . $suffix] = array(
				'data' => $data,
				'callbacks' => $callbacks
			);
		}
		$panelData['events'] = $events;

		$listeners = array();
		foreach ($controller->Crud->config('listeners') as $listener => $value) {
			$listeners[$listener] = $controller->Crud->listener($listener)->config();
		}
		$panelData['listeners'] = $listeners;

		$controller->set('crudDebugKitData', $panelData);
	}

/**
 * _getCallbacks
 *
 * Return all callbacks for a givent event key
 *
 * @param mixed $eventManager
 * @param mixed $eventKey
 * @return array
 */
	protected function _getCallbacks($eventManager, $eventKey) {
		$listeners = $eventManager->listeners($eventKey);
		foreach ($listeners as &$listener) {
			$listener = $listener['callable'];
			if (is_array($listener)) {
				$class = get_class($listener[0]);
				$method = $listener[1];
				$listener = "$class::$method";
			} elseif ($listener instanceof Closure) {
				$listener = $this->_getClosureSource($listener);
			}
		}

		return $listeners;
	}

/**
 * _getClosureSource
 *
 * Attempt to get the closure source, if it's not possible just return the object
 * in the full knowledge that it'll probably get dumped as the string "function"
 *
 * @param Closure $closure
 * @return array
 */
	protected function _getClosureSource(Closure $closure) {
		$exported = ReflectionFunction::export($closure, true);
		preg_match('#@@ (.*) (\d+) - (\d+)#', $exported, $match);
		if (!$match) {
			return $closure;
		}

		list($m, $file, $start, $end) = $match;

		$data = file($file);

		$lines = array();
		for ($i = $start - 1; $i < $end; $i++) {
			$string = $data[$i];
			$lines[] = $string;
		}

		return $lines;
	}

}
