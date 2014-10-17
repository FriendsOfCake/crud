<?php
namespace Crud\Core;

use Cake\Controller\Controller;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Crud\Event\Subject;

/**
 * Crud Base Class
 *
 * Implement base methods used in CrudAction and CrudListener classes
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class Object implements EventListenerInterface {

	use InstanceConfigTrait;
	use ProxyTrait;

/**
 * Container with reference to all objects
 * needed within the CrudListener and CrudAction
 *
 * @var \Cake\Controller\Controller
 */
	protected $_controller;

/**
 * Default configuration
 *
 * @var array
 */
	protected $_defaultConfig = [];

/**
 * Constructor
 *
 * @param \Crud\Event\Subject $subject
 * @param array $defaults Default settings
 * @return void
 */
	public function __construct(Controller $Controller, $config = []) {
		$this->_controller = $Controller;
		$this->config($config);
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
 * Convenient method for Request::is
 *
 * @param  string|array $method
 * @return boolean
 */
	protected function _checkRequestType($method) {
		return $this->_request()->is($method);
	}

}
