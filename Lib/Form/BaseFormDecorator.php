<?php
class BaseFormDecorator {

	protected $_Collection;

	protected $_settings;

	protected $Controller;

	protected $action;

	public function __construct($Collection, $settings) {
		$this->_Collection	= $Collection;
		$this->_settings	= $settings;
	}

	public function init(Controller $Controller, $action) {
		$this->Controller	= $Controller;
		$this->action		= $action;
	}

	public function beforeSave() {

	}

	public function beforeFind($query) {
		return $query;
	}

	public function afterFind($data) {
		return $data;
	}

	public function afterSave($success, $id = null) {

	}

	public function beforeRender() {

	}

	public function beforeDelete($id) {

	}

	public function afterDelete($id, $success = false) {

	}

	public function recordNotFound($id) {

	}

	public function beforePaginate() {

	}

	/**
	 * Check about they called action, is whitelisted or blacklisted
	 * Depening on the mode.
	 *
	 * Modes:
	 * only => only if in array (whitelist)
	 * not	=> only if NOT in array (blacklist)
	 *
	 * @param string $mode
	 * @param mixed $actions
	 *
	 * @return boolean
	 */
	protected function shouldProcess($mode, $actions = array()) {
		if(is_string($actions)) {
			$actions = array($actions);
		}

		switch ($mode) {
			case 'only':
				return in_array($this->action, $actions);
				break;

			case 'not':
				return !in_array($this->action, $actions);
				break;

			default:
				throw new Exception('Invalid mode');
				break;
		}
	}
}