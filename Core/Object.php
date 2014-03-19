<?php
namespace Crud\Core;

use Cake\Event\Event;
use Cake\Event\EventListener;
use Cake\Utility\Inflector;
use Cake\Controller\Controller;
use Crud\Event\Subject;

/**
 * Crud Base Class
 *
 * Implement base methods used in CrudAction and CrudListener classes
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class Object extends \Cake\Core\Object implements EventListener {

	use ProxyTrait;
	use ConfigTrait;

/**
 * Container with reference to all objects
 * needed within the CrudListener and CrudAction
 *
 * @var \Cake\Controller\Controller
 */
	protected $_controller;

/**
 * Constructor
 *
 * @param \Crud\Event\Subject $subject
 * @param array $defaults Default settings
 * @return void
 */
	public function __construct(Controller $Controller, $defaults = []) {
		$this->_controller = $Controller;

		if (!empty($defaults)) {
			$this->config($defaults);
		}
	}

/**
 * List of implemented events
 *
 * @return array
 */
	public function implementedEvents() {
		return [];
	}

/**
 * Return the human name of the model
 *
 * By default it uses Inflector::humanize, but can be changed
 * using the "name" configuration property
 *
 * @return string
 */
	protected function _getResourceName() {
		if (empty($this->_settings['name'])) {
			$this->_settings['name'] = strtolower(Inflector::humanize(Inflector::underscore($this->_repository()->alias())));
		}

		return $this->_settings['name'];
	}

/**
 * Convenient method for Request::is
 *
 * @param  string|array $method
 * @return boolean
 */
	protected function _checkRequestType($method) {
		return $this->_request()->is($method);
	}

}
