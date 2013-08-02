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

		$options = 0;
		if (version_compare(PHP_VERSION, '5.4.0', '>=') && Configure::read('debug')) {
			$options = $options | JSON_PRETTY_PRINT;
		}

		return json_encode($data, $options);
	}

}
