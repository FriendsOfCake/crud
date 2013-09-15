<?php
class RedirectionListener extends CrudListener {

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

		foreach ($this->action()->config('redirect') as $redirect) {
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
		switch ($type) {
			case 'request':
				return $this->_request()->{$key};

			case 'request.data':
				return $this->_request()->data($key);

			case 'request.query':
				return $this->_request()->query($key);

			case 'model.data':
				return Hash::get($this->_model()->data, $key);

			case 'model.field':
				return $this->_model()->field($key);

			case 'subject':
				if (isset($subject->{$key})) {
					return $subject->{$key};
				}

			default:
				throw new Exception('Unknown key type: ' . $type);
		}
	}

}
