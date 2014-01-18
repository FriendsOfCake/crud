<?php
namespace Crud\Traits;

trait FindMethodTrait {

/**
 * Change the find() method
 *
 * If `$method` is NULL the current value is returned
 * else the `findMethod` is changed
 *
 * @param mixed $method
 * @return mixed
 */
	public function findMethod($method = null) {
		if ($method === null) {
			return $this->config('findMethod');
		}

		return $this->config('findMethod', $method);
	}

}
