<?php

namespace Crud\Core;

use \Cake\Utility\Hash;

trait ConfigTrait {

/**
 * Instance configuration
 *
 * @var array
 */
	protected $_settings = array();

/**
 * Sets a configuration variable into this action
 *
 * If called with no arguments, all configuration values are
 * returned.
 *
 * $key is interpreted with dot notation, like the one used for
 * Configure::write()
 *
 * If $key is string and $value is not passed, it will return the
 * value associated with such key.
 *
 * If $key is an array and $value is empty, then $key will
 * be interpreted as key => value dictionary of settings and
 * it will be merged directly with $this->settings
 *
 * If $key is a string, the value will be inserted in the specified
 * slot as indicated using the dot notation
 *
 * @param mixed $key
 * @param mixed $value
 * @param boolean $merge
 * @return mixed|CrudAction
 */
	public function config($key = null, $value = null, $merge = true) {
		if ($key === null && $value === null) {
			return $this->_settings;
		}

		if ($value === null) {
			if (is_array($key)) {
				if ($merge) {
					$this->_settings = Hash::merge($this->_settings, $key);
				} else {
					foreach (Hash::flatten($key) as $k => $v) {
						$this->_settings = Hash::insert($this->_settings, $k, $v);
					}
				}

				return $this;
			}

			return Hash::get($this->_settings, $key);
		}

		if (is_array($value)) {
			if ($merge) {
				$value = array_merge((array)Hash::get($this->_settings, $key), $value);
			} else {
				foreach ($value as $k => $v) {
					$this->_settings = Hash::insert($this->_settings, $k, $v);
				}
			}
		}

		$this->_settings = Hash::insert($this->_settings, $key, $value);
		return $this;
	}

}
