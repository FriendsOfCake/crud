<?php
class RedirectionListener extends CrudListener {

	protected $_settings = array(
		'accessors' => array()
	);

	public function setup() {
		$this->_settings['accessors']['request'] = function(CrudSubject $subject, $key = null) {
			return $subject->request->{$key};
		};

		$this->_settings['accessors']['request.data'] = function(CrudSubject $subject, $key = null) {
			return $subject->request->data($key);
		};

		$this->_settings['accessors']['request.query'] = function(CrudSubject $subject, $key = null) {
			return $subject->request->query($key);
		};

		$this->_settings['accessors']['model.data'] = function(CrudSubject $subject, $key = null) {
			return Hash::get($subject->model->data, $key);
		};

		$this->_settings['accessors']['model.field'] = function(CrudSubject $subject, $key = null) {
			return $subject->model->field($key);
		};

		$this->_settings['accessors']['subject'] = function(CrudSubject $subject, $key = null) {
			if (isset($subject->{$key})) {
				return $subject->{$key};
			}

			return null;
		};
	}

/**
 * Add a new accessor
 *
 * @param string $key
 * @param Closure $accessor
 * @return void
 */
	public function accessor($key, Closure $accessor) {
		$this->_settings['accessors'][$key] = $accessor;
	}

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

		foreach ($this->_action()->config('redirect') as $redirect) {
			if (!$this->_getKey($subject, $redirect['type'], $redirect['key'])) {
				continue;
			}

			$subject->url = $this->_getUrl($subject, $redirect);
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
	protected function _getUrl(CrudSubject $subject, $config) {
		$url = $config['url'];

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
 * Types:
 *  - `request`: Access a property directly on the CakeRequest object
 *  - `request.data`: Read a `$key` in the CakeRequest->data array
 *  - `request.query`: Read a `$key` in the CakeRequest->query array
 *  - `model.data`: Read a `$key` in the Model->data array
 *  - `model.field`: Read a `$key` using `Model->field($key)`
 *  - `subject`: Read a `$key` directly on `$event->subject`
 *
 * @param CrudSubject $subject
 * @param string $type
 * @param string $key
 * @return mixed
 */
	protected function _getKey(CrudSubject $subject, $type, $key) {
		if (!array_key_exists($type, $this->_settings['accessors'])) {
			throw new Exception('Invalid type: '.  $type);
		}

		return $this->_settings['accessors'][$type]($subject, $key);
	}

}
