<?php
namespace Crud\Action;

use Cake\Error\NotImplementedException;
use Cake\Event\Event;
use Cake\Utility\Hash;
use Cake\Utility\String;
use Crud\Controller\Component\CrudComponent;
use Crud\Core\Object;
use Crud\Event\Subject;

/**
 * Base Crud class
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class Base extends Object {

/**
 * Startup method
 *
 * Called when the action is loaded
 *
 * @param \Crud\Event\Subject $subject
 * @param array $defaults
 * @return void
 */
	public function __construct(CrudComponent $Crud, Subject $subject, array $defaults = []) {
		parent::__construct($Crud, $subject, $defaults);
		$this->_settings['action'] = $subject->action;
	}

/**
 * Handle callback
 *
 * Based on the requested controller action,
 * decide if we should handle the request or not.
 *
 * By returning false the handling is cancelled and the
 * execution flow continues
 *
 * @throws NotImplementedException if the action can't handle the request
 * @param \Cake\Event\Event $Event
 * @return mixed
 */
	public function handle(Event $Event) {
		if (!$this->config('enabled')) {
			return false;
		}

		$requestMethod = $this->_request()->method();
		$method = '_' . strtolower($requestMethod);

		if (method_exists($this, $method)) {
			return call_user_func_array([$this, $method], $Event->subject->args);
		}

		if (method_exists($this, '_handle')) {
			return call_user_func_array([$this, '_handle'], $Event->subject->args);
		}

		throw new NotImplementedException(sprintf('Action %s does not implement a handler for HTTP verb %s', get_class($this), $requestMethod));
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

		$this->_session()->setFlash($subject->text, $subject->element, $subject->params, $subject->key);
	}

/**
 * Is the passed ID valid ?
 *
 * By default we assume you want to validate an numeric string
 * like a normal incremental ids from MySQL
 *
 * Change the validateId settings key to "uuid" for UUID check instead
 *
 * @param mixed $id
 * @return boolean
 * @throws BadRequestException If id is invalid
 */
	protected function _validateId($id) {
		$type = $this->config('validateId');

		if ($type === null) {
			$type = $this->detectPrimaryKeyFieldType();
		}

		if (!$type) {
			return true;
		} elseif ($type === 'uuid') {
			$valid = Validation::uuid($id);
		} else {
			$valid = is_numeric($id);
		}

		if ($valid) {
			return true;
		}

		$this->_trigger('invalidId', compact('id'));

		$message = $this->message('invalidId');
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
	}

/**
 * Automatically detect primary key data type for `_validateId()`
 *
 * Binary or string with length of 36 chars will be detected as UUID
 * If the primary key is a number, integer validation will be used
 *
 * If no reliable detection can be made, no validation will be made
 *
 * @param Model $model
 * @return string
 * @throws CakeException If unable to get model object
 */
	public function detectPrimaryKeyFieldType(Model $model = null) {
		if (empty($model)) {
			$repository = $this->_repository();
			if (empty($repository)) {
				throw new \Cake\Error\Exception('Missing repository object, cant detect primary key field type');
			}
		}

		$fInfo = $repository->schema()->column($repository->primaryKey());
		if (empty($fInfo)) {
			return false;
		}

		if ($fInfo['type'] == 'uuid') {
			return 'uuid';
		}

		if ($fInfo['type'] === 'integer') {
			return 'integer';
		}

		return false;
	}

	public function scope() {
		return $this->config('scope');
	}

}
