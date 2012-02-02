<?php
App::uses('ObjectCollection', 'Utility');

/**
 * The Crud collection
 *
 * Based on the Cake 2.x ObjectCollection with a twist for loading Form Decorators
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 */
class CrudCollection extends ObjectCollection {
	/**
	* Load a list of Form decorators
	*
	* @platform
	* @param array $items
	* @return void
	*/
	public function loadAll($items = array()) {
		$items = Set::normalize($items);
		foreach ($items as $decorator => $settings) {
			$settings = (array)$settings;
			$this->load($decorator, $settings);
		}
	}

	/**
	* Load a Form decorator
	*
	* @platform
	* @param string $decorator
	* @param array $settings
	* @return Object
	*/
	public function load($decorator, $settings = array()) {
		if (is_array($settings) && isset($settings['className'])) {
			$alias = $decorator;
			$decorator = $settings['className'];
		}

		list($plugin, $name) = pluginSplit($decorator, true);
		if (!isset($alias)) {
			$alias = $name;
		}

		if (isset($this->_loaded[$alias])) {
			return $this->_loaded[$alias];
		}

		$decoratorClass = $name . 'FormDecorator';
		App::uses($decoratorClass, $plugin . 'Form');
		if (!class_exists($decoratorClass)) {
			throw new CakeException(array(
				'file' => Inflector::underscore($decoratorClass) . '.php',
				'class' => $decoratorClass
			));
		}

		$this->_loaded[$alias] = new $decoratorClass($this, $settings);
		$enable = isset($settings['enabled']) ? $settings['enabled'] : true;
		if ($enable) {
			$this->enable($alias);
		} else {
			$this->disable($alias);
		}

		return true;
	}
}
