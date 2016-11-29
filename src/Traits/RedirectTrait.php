<?php
namespace Crud\Traits;

use Crud\Event\Subject;

trait RedirectTrait
{

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
    public function redirectConfig($name = null, $config = null)
    {
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
     * @param string|null $default Default URL to use redirect_url is not found in request or data
     * @return mixed
     */
    protected function _refererRedirectUrl($default = null)
    {
        $controller = $this->_controller();

        return $this->_redirectUrl($controller->referer($default, true));
    }

    /**
     * Returns the _redirect_url for this request.
     *
     * @param string|null $default Default URL to use if _redirect_url if not found in request or data.
     * @return mixed
     */
    protected function _redirectUrl($default = null)
    {
        $request = $this->_request();

        if (!empty($request->data['_redirect_url'])) {
            return $request->data['_redirect_url'];
        }
        if (!empty($request->query['_redirect_url'])) {
            return $request->query['_redirect_url'];
        }
        if (!empty($request->data['redirect_url'])) {
            return $request->data['redirect_url'];
        }
        if (!empty($request->query['redirect_url'])) {
            return $request->query['redirect_url'];
        }

        return $default;
    }

    /**
     * Called for all redirects inside CRUD
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param string|array|null $url URL
     * @param int|null $status Status code
     * @return \Cake\Network\Response
     */
    protected function _redirect(Subject $subject, $url = null, $status = null)
    {
        $url = $this->_redirectUrl($url);

        $subject->url = $url;
        $subject->status = $status;
        $event = $this->_trigger('beforeRedirect', $subject);

        if ($event->isStopped()) {
            return $this->_controller()->response;
        }

        return $this->_controller()->redirect($subject->url, $subject->status);
    }
}
