<?php
namespace Crud\Traits;

use Crud\Event\Subject;

trait RedirectTrait {

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
 * Returns the redirect_url for this request, with a fallback to the referring page
 *
 * @param string $default Default URL to use redirect_url is not found in request or data
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

/**
 * Called for all redirects inside CRUD
 *
 * @param \Crud\Event\Subject $subject Event subject
 * @param string|array $url URL
 * @param integer $status Status code
 * @param boolean $exit Whether to exit script or not
 * @return void
 */
	protected function _redirect(Subject $subject, $url = null, $status = null, $exit = true) {
		$url = $this->_redirectUrl($url);

		$subject->url = $url;
		$subject->status = $status;
		$subject->exit = $exit;
		$this->_trigger('beforeRedirect', $subject);

		$controller = $this->_controller();
		$controller->redirect($subject->url, $subject->status, $subject->exit);
		return $controller->response;
	}

}
