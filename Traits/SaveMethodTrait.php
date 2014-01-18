<?php
namespace Crud\Traits;

trait SaveMethodTrait {

/**
 * Change the save() method
 *
 * If `$method` is NULL the current value is returned
 * else the `saveMethod` is changed
 *
 * @param mixed $method
 * @return mixed
 */
	public function saveMethod($method = null) {
		if ($method === null) {
			return $this->config('saveMethod');
		}

		return $this->config('saveMethod', $method);
	}

}
