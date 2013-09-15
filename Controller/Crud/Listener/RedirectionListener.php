<?php

App::uses('CakeEvent', 'Event');
App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * Enabled Crud to respond in a computer readable format like JSON or XML
 *
 * It tries to enforce some REST principles and keep some string conventions in the output format
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RedirectionListener extends CrudListener {

/**
 * Settings
 *
 * @var array
 */
	protected $_settings = array(
		'readers' => array()
	);

/**
 * Returns a list of all events that will fire in the controller during its lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.beforeRedirect' => array('callable' => 'redirect', 'priority' => 90)
		);
	}

/**
 * Setup method
 *
 * Called when the listener is initialized
 *
 * Setup the default readers
 *
 * @return void
 */
	public function setup() {
		$this->reader('request.key', function(CrudSubject $subject, $key = null) {
			if (!isset($subject->request->{$key})) {
				return null;
			}

			return $subject->request->{$key};
		});

		$this->reader('request.data', function(CrudSubject $subject, $key = null) {
			return $subject->request->data($key);
		});

		$this->reader('request.query', function(CrudSubject $subject, $key = null) {
			return $subject->request->query($key);
		});

		$this->reader('model.key', function(CrudSubject $subject, $key = null) {
			if (!isset($subject->model->{$key})) {
				return null;
			}

			return $subject->model->{$key};
		});

		$this->reader('model.data', function(CrudSubject $subject, $key = null) {
			return Hash::get($subject->model->data, $key);
		});

		$this->reader('model.field', function(CrudSubject $subject, $key = null) {
			return $subject->model->field($key);
		});

		$this->reader('subject.key', function(CrudSubject $subject, $key = null) {
			if (!isset($subject->{$key})) {
				return null;
			}

			return $subject->{$key};
		});
	}

/**
 * Add or replace an reader
 *
 * @param string $key
 * @param null|Closure $reader
 * @return mixed
 */
	public function reader($key, Closure $reader = null) {
		if ($reader === null) {
			return $this->config('readers.' . $key);
		}

		return $this->config('readers.' . $key, $reader);
	}

/**
 * Redirect callback
 *
 * If a special redirect key is provided, change the
 * redirection URL target
 *
 * @param CakeEvent $event
 * @return void
 */
	public function redirect(CakeEvent $event) {
		$subject = $event->subject;

		$redirects = $this->_action()->config('redirect');
		if (empty($redirects)) {
			return;
		}

		foreach ($redirects as $redirect) {
			if (!$this->_getKey($subject, $redirect['type'], $redirect['key'])) {
				continue;
			}

			$subject->url = $this->_getUrl($subject, $redirect['url']);
			break;
		}
	}

/**
 * Get the new redirect URL
 *
 * Expand configurations where possible and replace the
 * placeholder with the actual value
 *
 * @param CrudSubject $subject
 * @param array $config
 * @return array
 */
	protected function _getUrl(CrudSubject $subject, $url) {
		foreach ($url as $key => $value) {
			if (!is_array($value)) {
				continue;
			}

			$url[$key] = $this->_getKey($subject, $value[0], $value[1]);
		}

		return $url;
	}

/**
 * Return the value of `$type` with `$key`
 *
 * @throws Exception if the reader is invalid
 * @param CrudSubject $subject
 * @param string $reader
 * @param string $key
 * @return mixed
 */
	protected function _getKey(CrudSubject $subject, $reader, $key) {
		$callable = $this->reader($reader);

		if ($callable === null || !is_callable($callable)) {
			throw new Exception('Invalid type: ' . $reader);
		}

		return $callable($subject, $key);
	}

}
