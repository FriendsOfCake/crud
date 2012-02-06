<?php
namespace Crud;

/**
 * The Base Form Decorator
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
abstract class BaseEvent implements \CakeEventListener {

	/**
	* A reference the Controller that created the CrudCollection
	*
	* @var Controller
	*/
	protected $controller;

	/**
	* A reference to the Controller action for the request
	*
	* @var string
	*/
	protected $action;

	/**
	 * Returns a list of all events that will fire in the controller during it's lifecycle.
	 * You can override this function to add you own listener callbacks
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return array(
			'Crud.init'				=> array('callable' => 'init', 'passParams' => true),

			'Crud.beforePaginate'	=> 'beforePaginate',
			'Crud.afterPaginate'	=> array('callable' => 'afterPaginate', 'passParams' => true),

			'Crud.recordNotFound'	=> array('callable' => 'recordNotFound', 'passParams' => true),
			'Crud.invalidId'		=> array('callable' => 'invalidId', 'passParams' => true),

			'Crud.beforeRender'		=> 'beforeRender',
			'Crud.beforeRedirect'	=> array('callable' => 'beforeRedirect', 'passParams' => true),

			'Crud.beforeSave'		=> 'beforeSave',
			'Crud.afterSave'		=> array('callable' => 'afterSave', 'passParams' => true),

			'Crud.beforeFind'		=> array('callable' => 'beforeFind', 'passParams' => true),
			'Crud.afterFind'		=> array('callable' => 'afterFind', 'passParams' => true),

			'Crud.beforeDelete'		=> array('callable' => 'beforeDelete', 'passParams' => true),
			'Crud.afterDelete'		=> 'afterDelete'
		);
	}

	/**
	* Initialize method
	*
	* Called before any other method in the decorator
	*
	* Just set the arguments as instance properties for easier access later
	*
	* @param Controller $controller
	* @param string $action
	* @return void
	*/
	public function init(\Controller $controller, $action) {
		$this->controller	= $controller;
		$this->action		= $action;
	}

	/**
	* Called before a record is saved in add or edit actions
	*
	* @return void
	*/
	public function beforeSave() {

	}

	/**
	* Called before any CRUD redirection
	*
	* @return void
	*/
	public function beforeRedirect($url = null) {
		return $url;
	}

	/**
	* Called before any find() on the model
	*
	* Must *always* return an array
	*
	* @param array $query Array with contain, conditions, fields, sort ect.
	* @return array
	*/
	public function beforeFind($query) {
		return $query;
	}

	/**
	* After find callback
	*
	* Must *always* return an array
	*
	* @param array $data Array with model data from find()
	* @return array
	*/
	public function afterFind($data) {
		return $data;
	}

	/**
	* Called after any save() method
	*
	* @param boolean $success Was the save successful ?
	* @param string $id The ID of the new record if save was successful
	* @return void
	*/
	public function afterSave($success, $id = null) {

	}

	/**
	* Called before cake's own render()
	*
	* @return void
	*/
	public function beforeRender() {

	}

	/**
	* Called before any delete() action
	*
	* @param string $id The ID of the record that will be deleted
	* @return void
	*/
	public function beforeDelete($id) {

	}

	/**
	* Called after any delete() action
	*
	* @param boolean $success Was the delete successful ?
	* @param string $id The ID of the deleted record
	* @return void
	*/
	public function afterDelete($success, $id) {

	}

	/**
	* Called if a find() did not return any records
	*
	* @param string $id The ID of the record we tried to find
	* @return void
	*/
	public function recordNotFound($id) {

	}

	/**
	* Called right before any paginate() method
	*
	* @return void
	*/
	public function beforePaginate() {

	}

	/**
	* Called right after any paginate() method
	*
	* @return void
	*/
	public function afterPaginate($result) {
		return $result;
	}

	/**
	* Called if the ID format validation failed
	*
	* @return void
	*/
	public function invalidId($id) {

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
				throw new \Exception('Invalid mode');
				break;
		}
	}
}