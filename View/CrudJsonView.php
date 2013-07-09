<?php

App::uses('View', 'View');
App::uses('JsonView', 'View');

/**
 * CrudApiView
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudJsonView extends JsonView {

/**
 * Serialize view vars
 *
 * This is only here to follow CakePHP 2.4 compatibility with _serialize aliasing
 *
 * @param array $serialize The viewVars that need to be serialized
 * @return string The serialized data
 */
	protected function _serialize($serialize) {
		if (is_array($serialize)) {
			$data = array();
			foreach ($serialize as $alias => $key) {
				if (is_numeric($alias)) {
					$alias = $key;
				}

				$data[$alias] = $this->viewVars[$key];
			}
		} else {
			$data = isset($this->viewVars[$serialize]) ? $this->viewVars[$serialize] : null;
		}

		return json_encode($data);
	}

}
