<?php

namespace Crud\Log;

class QueryLogger extends \Cake\Database\Log\QueryLogger {

	protected $_logs = [];

	public function getLogs() {
		return $this->_logs;
	}

/**
 * Wrapper function for the logger object, useful for unit testing
 * or for overriding in subclasses.
 *
 * @param LoggedQuery $query to be written in log
 * @return void
 */
	protected function _log($query) {
		$this->_logs[] = $query;
		parent::_log($query);
	}

}
