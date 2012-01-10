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
}