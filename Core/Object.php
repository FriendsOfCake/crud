<?php

namespace Crud\Core;

use \Cake\Event\EventListener;
use \Cake\Event\Event;
use \Crud\Event\Subject;

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
 * @var CrudSubject
 */
	protected $_container;

/**
 * Constructor
 *
 * @param \Crud\Event\Subject $subject
 * @param array $defaults Default settings
 * @return void
 */
	public function __construct(Subject $subject, $defaults = array()) {
		$this->_container = $subject;

		if (!empty($defaults)) {
			$this->config($defaults);
		}
	}

/**
 * initialize callback
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeHandle(Event $event) {
		$this->_container = $event->subject;
	}

/**
 * Returns a list of all events that will fire during the objects lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.initialize' => 'initialize'
		);
	}

/**
 * Returns the redirect_url for this request, with a fallback to the referring page
 *
 * @param string $default Default URL to use redirect_url is not found in request or data
 * @param boolean $local If true, restrict referring URLs to local server
 * @return mixed
 */
	protected function _refererRedirectUrl($default = null) {
		$controller = $this->_controller();
		return $this->_redirectUrl($controller->referer($default, true));
	}

/**
 * Returns the redirect_url for this request.
 *
 * @param string $default Default URL to use redirect_url is not found in request or data
 * @return mixed
 */
	protected function _redirectUrl($default = null) {
		$url = $default;
		$request = $this->_request();
		if (!empty($request->data['redirect_url'])) {
			$url = $request->data['redirect_url'];
		} elseif (!empty($request->query['redirect_url'])) {
			$url = $request->query['redirect_url'];
		}

		return $url;
	}

}
