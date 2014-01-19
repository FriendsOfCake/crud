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

/**
 * Change the saveOptions configuration
 *
 * This is the 2nd argument passed to saveAll()
 *
 * if `$config` is NULL the current config is returned
 * else the `saveOptions` is changed
 *
 * @param mixed $config
 * @return mixed
 */
	public function saveOptions($config = null) {
		if (empty($config)) {
			return $this->config('saveOptions');
		}

		return $this->config('saveOptions', $config);
	}

}
