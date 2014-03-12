<?php
namespace Crud\Traits;

trait ViewTrait {

/**
 * Change the view to be rendered
 *
 * If `$view` is NULL the current view is returned
 * else the `$view` is changed
 *
 * If no view is configured, it will use the action
 * name from the request object
 *
 * @param mixed $view
 * @return mixed
 */
	public function view($view = null) {
		if (empty($view)) {
			return $this->config('view') ?: $this->_request()->action;
		}

		return $this->config('view', $view);
	}

}
