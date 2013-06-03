<?php
/**
 * Crud subject
 *
 * All Crud.* events passes this object as subject
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 */
class CrudSubject extends stdClass {

/**
 * Constructor
 *
 * @param array $fields
 * @return void
 */
	public function __construct($fields = array()) {
		$this->set($fields);
	}

/**sh
 * Set a list of key / values to the stdClass
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
 *
 * @return boolean
 */
	public function shouldProcess($mode, $actions = array()) {
		if (is_string($actions)) {
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
		}
	}

}
