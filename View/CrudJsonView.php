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
class CrudJsonView extends View {

/**
 * Render a JSON view.
 *
 * CRUD specific changes compared to CakePHP build-in:
 *  - View helpers will be loaded even though _serialize is defined
 *  - beforeRender and afterRender events will be emitted
 *
 * ### Special parameters
 * `_serialize` To convert a set of view variables into a JSON response.
 *   It's value can be a string for single variable name or array for multiple names.
 *   You can omit the`_serialize` parameter, and use a normal view + layout as well.
 * `_jsonp` Enables JSONP support and wraps response in callback function provided in query string.
 *   - Setting it to true enables the default query string parameter "callback".
 *   - Setting it to a string value, uses the provided query string parameter for finding the
 *     JSONP callback name.
 *
 * @param string $view The view being rendered.
 * @param string $layout The layout being rendered.
 * @return string The rendered view.
 */
	public function render($view = null, $layout = null) {
		$return = null;
		if (!$this->_helpersLoaded) {
			$this->loadHelpers();
		}

		if (isset($this->viewVars['_serialize'])) {
			$this->getEventManager()->dispatch(new CakeEvent('View.beforeRender', $this, array(NULL)));
			$return = $this->_serialize($this->viewVars['_serialize']);
		} elseif ($view !== false && $this->_getViewFileName($view)) {
			$return = parent::render($view, false);
		}

		if (!empty($this->viewVars['_jsonp'])) {
			$jsonpParam = $this->viewVars['_jsonp'];
			if ($this->viewVars['_jsonp'] === true) {
				$jsonpParam = 'callback';
			}
			if (isset($this->request->query[$jsonpParam])) {
				$return = sprintf('%s(%s)', h($this->request->query[$jsonpParam]), $return);
				$this->response->type('js');
			}
		}

		if (isset($this->viewVars['_serialize'])) {
			$this->getEventManager()->dispatch(new CakeEvent('View.afterRender', $this, array(NULL)));
		}

		return $return;
	}

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

		return json_encode($data);
	}

}
