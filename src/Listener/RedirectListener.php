<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Event\EventInterface;
use Crud\Event\Subject;
use Exception;

/**
 * Redirect Listener
 *
 * Listener to improve upon the default redirection behavior of Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RedirectListener extends BaseListener
{
    /**
     * Settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'readers' => [],
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add your own listener callbacks
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Crud.beforeRedirect' => ['callable' => 'beforeRedirect', 'priority' => 90],
        ];
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
    public function setup(): void
    {
        $this->reader('request.key', function (Subject $subject, $key = null) {
            $request = $this->_request();

            return $request->getParam($key, null);
        });

        $this->reader('request.data', function (Subject $subject, $key = null) {
            $request = $this->_request();

            return $request->getData($key);
        });

        $this->reader('request.query', function (Subject $subject, $key = null) {
            $request = $this->_request();

            return $request->getQuery($key);
        });

        $this->reader('entity.field', function (Subject $subject, $key = null) {
            return $subject->entity->get($key);
        });

        $this->reader('subject.key', function (Subject $subject, $key = null) {
            if (!isset($subject->{$key})) {
                return null;
            }

            return $subject->{$key};
        });
    }

    /**
     * Add or replace a reader
     *
     * @param string $key Key
     * @param mixed $reader Reader
     * @return mixed
     */
    public function reader(string $key, $reader = null)
    {
        if ($reader === null) {
            return $this->getConfig('readers.' . $key);
        }

        return $this->setConfig('readers.' . $key, $reader);
    }

    /**
     * Redirect callback
     *
     * If a special redirect key is provided, change the
     * redirection URL target
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     * @throws \Exception
     */
    public function beforeRedirect(EventInterface $event): void
    {
        /** @var \Crud\Event\Subject $subject */
        $subject = $event->getSubject();

        $redirects = $this->_action()->redirectConfig();
        if (empty($redirects)) {
            return;
        }

        foreach ($redirects as $redirect) {
            if (!$this->_getKey($subject, $redirect['reader'], $redirect['key'])) {
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
     * @param \Crud\Event\Subject $subject Subject
     * @param array $url URL
     * @return array
     * @throws \Exception
     */
    protected function _getUrl(Subject $subject, array $url): array
    {
        foreach ($url as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if ($key === '?') {
                $url[$key] = $this->_getUrl($subject, $value);
                continue;
            }

            $url[$key] = $this->_getKey($subject, $value[0], $value[1]);
        }

        return $url;
    }

    /**
     * Return the value of `$type` with `$key`
     *
     * @param \Crud\Event\Subject $subject Subject
     * @param string $reader Reader
     * @param string $key Key
     * @return mixed
     * @throws \Exception if the reader is invalid
     */
    protected function _getKey(Subject $subject, string $reader, string $key)
    {
        $callable = $this->reader($reader);

        if ($callable === null || !is_callable($callable)) {
            throw new Exception('Invalid reader: ' . $reader);
        }

        return $callable($subject, $key);
    }
}
