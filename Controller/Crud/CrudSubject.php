<?php
/**
 * Crud subject
 *
 * All Crud.* events passes this object as subject
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudSubject {

/**
 * Instance of the crud component
 *
 * @var CrudComponent
 */
	public $crud;

/**
 * Instance of the controller
 *
 * @var Controller
 */
	public $controller;

/**
 * Name of the default controller model class
 *
 * @var string
 */
	public $modelClass;

/**
 * The default action model instance
 *
 * @var Model
 */
	public $model;

/**
 * Request object instance
 *
 * @var CakeRequest
 */
	public $request;

/**
 * Response object instance
 *
 * @var CakeResponse
 */
	public $response;

/**
 * The name of the action object associated with this dispatch
 *
 * @var string
 */
	public $action;

/**
 * Optional arguments passed to the controller action
 *
 * @var array
 */
	public $args;

/**
 * Constructor
 *
 * @param array $fields
 * @return void
 */
	public function __construct($fields = array()) {
		$this->set($fields);
	}

/**
 * Set a list of key / values for this object
 *
 * @param array $fields
 * @return void
 */
	public function set($fields) {
		foreach ($fields as $k => $v) {
			$this->{$k} = $v;
		}
	}

/**
 * Check about they called action, is white listed or blacklisted
 * depending on the mode.
 *
 * Modes:
 * only => only if in array (white list)
 * not	=> only if NOT in array (blacklist)
 *
 * @param string $mode
 * @param mixed $actions
 * @return boolean
 * @throws CakeException In case of invalid mode
 */
	public function shouldProcess($mode, $actions = array()) {
		if (is_string($actions)) {
			$actions = array($actions);
		}

		switch ($mode) {
			case 'only':
				return in_array($this->action, $actions);

			case 'not':
				return !in_array($this->action, $actions);

			default:
				throw new CakeException('Invalid mode');
		}
	}

}
