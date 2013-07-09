<?php

App::uses('View', 'View');
App::uses('XmlView', 'View');

/**
 * CrudApiView
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class CrudXmlView extends XmlView {

/**
 * Serialize view vars
 *
 * This is only here to follow CakePHP 2.4 compatibility with _serialize aliasing
 *
 * @param array $serialize The viewVars that need to be serialized
 * @return string The serialized data
 */
	protected function _serialize($serialize) {
		$rootNode = isset($this->viewVars['_rootNode']) ? $this->viewVars['_rootNode'] : 'response';

		if (is_array($serialize)) {
			$data = array($rootNode => array());
			foreach ($serialize as $alias => $key) {
				if (is_numeric($alias)) {
					$alias = $key;
				}

				$data[$rootNode][$alias] = $this->viewVars[$key];
			}
		} else {
			$data = isset($this->viewVars[$serialize]) ? $this->viewVars[$serialize] : null;
			if (is_array($data) && Set::numeric(array_keys($data))) {
				$data = array($rootNode => array($serialize => $data));
			}
		}

		return Xml::fromArray($data)->asXML();
	}

}
