<?php
namespace Crud\Traits;

use Crud\Event\Subject;

trait SerializeTrait {

/**
 * Change the serialize keys
 *
 * If `$keys` is NULL the current configuration is returned
 * else the `$serialize` configuration is changed.
 *
 * @param null|array $keys
 * @return mixed
 */
	public function serialize($keys = null) {
		if (is_null($keys)) {
			return (array)$this->config('serialize');
		}

		return $this->config('serialize', (array)$keys);
	}

}
