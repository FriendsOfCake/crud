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
     * @param string|null $name Name of the redirection rule
     * @param array|null $config Redirection configuration
     * @return mixed
     */
    public function redirectConfig(?string $name = null, ?array $config = null): mixed
    {
        if ($name === null && $config === null) {
            return $this->getConfig('redirect');
        }

        $path = sprintf('redirect.%s', $name);
        if ($config === null) {
            return $this->getConfig($path);
        }

        return $this->setConfig($path, $config);
    }

    /**
     * Returns the redirect_url for this request, with a fallback to the referring page
     *
     * @param array|string|null $default Default URL to use redirect_url is not found in request or data
     * @return array|string
     */
    protected function _refererRedirectUrl(array|string|null $default = null): array|string
    {
        return $this->_redirectUrl($this->_controller()->referer($default, true));
    }

    /**
     * Returns the _redirect_url for this request.
     *
     * @param array|string $default Default URL to use if _redirect_url if not found in request or data.
     * @return array|string
     */
    protected function _redirectUrl(array|string $default): array|string
    {
        $request = $this->_request();

        return $request->getData('_redirect_url')
            ?? $request->getQuery('_redirect_url')
            ?? $request->getData('redirect_url')
            ?? $request->getQuery('redirect_url')
            ?? $default;
    }

    /**
     * Called for all redirects inside CRUD
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param array|string $url URL
     * @param int $status Status code
     * @return \Cake\Http\Response|null
     */
    protected function _redirect(Subject $subject, string|array $url, int $status = 302): ?Response
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
