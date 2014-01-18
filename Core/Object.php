<?php
namespace Crud\Core;

use Cake\Event\Event;
use Cake\Event\EventListener;
use Cake\Utility\Inflector;
use Crud\Event\Subject;
use Crud\Controller\Component\CrudComponent;

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
 * @var Crud\Controller\Component\CrudComponent
 */
	protected $_crud;

/**
 * Constructor
 *
 * @param \Crud\Event\Subject $subject
 * @param array $defaults Default settings
 * @return void
 */
	public function __construct(CrudComponent $Crud, Subject $subject, $defaults = array()) {
		$this->_crud = $Crud;

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

}
