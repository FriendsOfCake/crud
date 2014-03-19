<?php
namespace Crud\Action;

use Cake\Controller\Controller;
use Cake\Error\NotImplementedException;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\Utility\String;
use Crud\Core\Object;
use Crud\Event\Subject;

/**
 * Base Crud class
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class Base extends Object {

	protected $_responding = false;

/**
 * Handle callback
 *
 * Based on the requested controller action,
 * decide if we should handle the request or not.
 *
 * By returning false the handling is canceled and the
 * execution flow continues
 *
 * @throws NotImplementedException if the action can't handle the request
 * @param array $args
 * @return mixed
 */
	public function handle($args = []) {
		if (!$this->enabled()) {
			return false;
		}

		if (!is_array($args)) {
			$args = (array)$args;
		}

		$method = '_' . strtolower($this->_request()->method());

		if (method_exists($this, $method)) {
			$this->_responding = true;
			$this->_controller()->getEventManager()->attach($this);
			return call_user_func_array([$this, $method], $args);
		}

		if (method_exists($this, '_handle')) {
			$this->_responding = true;
			$this->_controller()->getEventManager()->attach($this);
			return call_user_func_array([$this, '_handle'], $args);
		}

		throw new NotImplementedException(sprintf('Action %s does not implement a handler for HTTP verb %s', get_class($this), $requestMethod));
	}

	public function responding() {
		return (bool)$this->_responding;
	}

/**
 * Enable the Crud action
 *
 * @return void
 */
	public function enable() {
		$this->config('enabled', true);

		$controller = $this->_controller();
		$actionName = $this->config('action');

		if (!in_array($actionName, $controller->methods)) {
			$controller->methods[] = $actionName;
		}
	}

/**
 * Test if a Crud action is enabled or not
 *
 * @return boolean
 */
	public function enabled() {
		return $this->config('enabled');
	}

/**
 * Disable the Crud action
 *
 * @return void
 */
	public function disable() {
		$this->config('enabled', false);

		$controller = $this->_controller();
		$actionName = $this->config('action');

		$pos = array_search($actionName, $controller->methods);
		if ($pos !== false) {
			unset($controller->methods[$pos]);
		}
	}

/**
 * return the config for a given message type
 *
 * @param string $type
 * @param array $replacements
 * @return array
 * @throws CakeException for a missing or undefined message type
 */
	public function message($type, array $replacements = array()) {
		if (empty($type)) {
			throw new CakeException('Missing message type');
		}

		$crud = $this->_crud();

		$config = $this->config('messages.' . $type);
		if (empty($config)) {
			$config = $crud->config('messages.' . $type);
			if (empty($config)) {
				throw new CakeException(sprintf('Invalid message type "%s"', $type));
			}
		}

		if (is_string($config)) {
			$config = ['text' => $config];
		}

		$config = Hash::merge([
			'element' => 'default',
			'params' => ['class' => 'message'],
			'key' => 'flash',
			'type' => $this->config('action') . '.' . $type,
			'name' => $this->_getResourceName()
		], $config);

		if (!isset($config['text'])) {
			throw new \Exception(sprintf('Invalid message config for "%s" no text key found', $type));
		}

		$config['params']['original'] = ucfirst(str_replace('{name}', $config['name'], $config['text']));

		$domain = $this->config('messages.domain');
		if (!$domain) {
			$domain = $crud->config('messages.domain') ?: 'crud';
		}

		$config['text'] = __d($domain, $config['params']['original']);

		$config['text'] = String::insert(
			$config['text'],
			$replacements + ['name' => $config['name']],
			['before' => '{', 'after' => '}']
		);

		$config['params']['class'] .= ' ' . $type;
		return $config;
	}

/**
 * Wrapper for Session::setFlash
 *
 * @param string $type Message type
 * @return void
 */
	public function setFlash($type, $subject) {
		$subject->set($this->message($type));
		$event = $this->_trigger('setFlash', $subject);
		if ($event->isStopped()) {
			return;
		}

		$this->_controller()->Session->setFlash($subject->text, null, $subject->params, $subject->key);
	}

/**
 * Change redirect configuration
 *
 * If both `$name` and `$config` is empty all redirection
 * rules will be returned.
 *
 * If `$name` is provided and `$config` is null, the named
 * redirection configuration is returned.
 *
 * If both `$name` and `$config` is provided, the configuration
 * is changed for the named rule.
 *
 * $config should contain the following keys:
 *  - type : name of the reader
 *  - key  : the key to read inside the reader
 *  - url  : the URL to redirect to
 *
 * @param null|string $name Name of the redirection rule
 * @param null|array $config Redirection configuration
 * @return mixed
 */
	public function redirectConfig($name = null, $config = null) {
		if ($name === null && $config === null) {
			return $this->config('redirect');
		}

		$path = sprintf('redirect.%s', $name);
		if ($config === null) {
			return $this->config($path);
		}

		return $this->config($path, $config);
	}

/**
 * Get the action scope
 *
 * Usually it's 'table' or 'entity'
 *
 * @return string
 */
	public function scope() {
		return $this->config('scope');
	}

	public function publishSuccess(\Cake\Event\Event $event) {
		if (!isset($event->subject->success)) {
			return false;
		}

		$this->_controller()->set('success', $event->subject->success);
	}

/**
 * Additional auxiliary events emitted if certain traits are loaded
 *
 * @return array
 */
	public function implementedEvents() {
		$events = parent::implementedEvents();

		$events['Crud.beforeRender'][] = ['callable' => [$this, 'publishSuccess']];

		if (method_exists($this, 'viewVar')) {
			$events['Crud.beforeRender'][] = ['callable' => [$this, 'publishViewVar']];
		}

		return $events;
	}

}
