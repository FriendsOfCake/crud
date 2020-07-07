<?php
declare(strict_types=1);

namespace Crud\Traits;

use Cake\Http\Response;
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
    public function redirectConfig(?string $name = null, ?array $config = null)
    {
        if ($name === null && $config === null) {
            return $this->getConfig('redirect');
        }

        /** @psalm-suppress PossiblyNullArgument */
        $path = sprintf('redirect.%s', $name);
        if ($config === null) {
            return $this->getConfig($path);
        }

        return $this->setConfig($path, $config);
    }

    /**
     * Returns the redirect_url for this request, with a fallback to the referring page
     *
     * @param string|null $default Default URL to use redirect_url is not found in request or data
     * @return mixed
     */
    protected function _refererRedirectUrl(?string $default = null)
    {
        $controller = $this->_controller();

        return $this->_redirectUrl($controller->referer($default, true));
    }

    /**
     * Returns the _redirect_url for this request.
     *
     * @param string|array|null $default Default URL to use if _redirect_url if not found in request or data.
     * @return mixed
     */
    protected function _redirectUrl($default = null)
    {
        $request = $this->_request();

        if (!empty($request->getData('_redirect_url'))) {
            return $request->getData('_redirect_url');
        }
        if (!empty($request->getQuery('_redirect_url'))) {
            return $request->getQuery('_redirect_url');
        }
        if (!empty($request->getData('redirect_url'))) {
            return $request->getData('redirect_url');
        }
        if (!empty($request->getQuery('redirect_url'))) {
            return $request->getQuery('redirect_url');
        }

        return $default;
    }

    /**
     * Called for all redirects inside CRUD
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param string|array|null $url URL
     * @param int $status Status code
     * @return \Cake\Http\Response|null
     */
    protected function _redirect(Subject $subject, $url = null, int $status = 302): ?Response
    {
        $url = $this->_redirectUrl($url);

        $subject->url = $url;
        $subject->status = $status;
        $event = $this->_trigger('beforeRedirect', $subject);

        if ($event->isStopped()) {
            return $this->_controller()->getResponse();
        }

        return $this->_controller()->redirect($subject->url, $subject->status);
    }
}
