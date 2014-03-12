<?php
namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;

/**
 * When loaded Crud API will include query logs in the response
 *
 * Very much like the DebugKit version, the SQL log will only be appended
 * if the following conditions is true:
 *  1) The request must be 'api' (.json/.xml)
 *  2) The debug level must be 2 or above
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLog extends Base {

/**
 * Returns a list of all events that will fire in the controller during its lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 10 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		if (!$this->_checkRequestType('api')) {
			return [];
		}

		return [
			'Crud.initialize' => ['callable' => [$this, 'setupLogging'], 'priority' => 1],
			'Crud.beforeRender' => ['callable' => [$this, 'beforeRender'], 'priority' => 75]
		];
	}

/**
 * Setup logging for all connections
 *
 * @param  Event  $event
 * @return void
 */
	public function setupLogging(Event $event) {
		foreach ($this->_getSources() as $connectionName) {
			$this->_getSource($connectionName)->logQueries(true);
			$this->_getSource($connectionName)->logger(new \Crud\Log\QueryLogger());
		}
	}

/**
 * Appends the query log to the JSON or XML output
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(Event $event) {
		if (Configure::read('debug') < 2) {
			return;
		}

		$this->_action()->config('serialize.queryLog', 'queryLog');
		$this->_controller()->set('queryLog', $this->_getQueryLogs());
	}

/**
 * Get the query logs for all sources
 *
 * @return array
 */
	protected function _getQueryLogs() {
		$sources = $this->_getSources();

		$queryLog = [];
		foreach ($sources as $source) {
			$queryLog[$source] = $this->_getSource($source)->logger()->getLogs();
		}

		return $queryLog;
	}

/**
 * Get a list of sources defined in database.php
 *
 * @codeCoverageIgnore
 * @return array
 */
	protected function _getSources() {
		return ConnectionManager::configured();
	}

/**
 * Get a specific data source
 *
 * @codeCoverageIgnore
 * @param string $source Datasource name
 * @return DataSource
 */
	protected function _getSource($source) {
		return ConnectionManager::get($source);
	}

}
