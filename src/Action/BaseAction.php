<?php
namespace Crud\Action;

use Cake\Event\Event;
use Cake\Http\Exception\NotImplementedException;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Crud\Core\BaseObject;
use Crud\Event\Subject;

/**
 * Base Crud class
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseAction extends BaseObject
{

    /**
     * Check if the current action is responding to a request or not
     *
     * @var bool
     */
    protected $_responding = false;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Handle callback
     *
     * Based on the requested controller action,
     * decide if we should handle the request or not.
     *
     * By returning false the handling is canceled and the
     * execution flow continues
     *
     * @param array $args Arguments
     * @return mixed
     * @throws \Cake\Http\Exception\NotImplementedException if the action can't handle the request
     */
    public function handle($args = [])
    {
        if (!$this->enabled()) {
            return false;
        }

        if (!is_array($args)) {
            $args = (array)$args;
        }

        $method = '_' . strtolower($this->_request()->getMethod());

        if (method_exists($this, $method)) {
            $this->_responding = true;
            $this->_controller()->getEventManager()->on($this);

            return call_user_func_array([$this, $method], $args);
        }

        if (method_exists($this, '_handle')) {
            $this->_responding = true;
            $this->_controller()->getEventManager()->on($this);

            return call_user_func_array([$this, '_handle'], $args);
        }

        throw new NotImplementedException(sprintf(
            'Action %s does not implement a handler for HTTP verb %s',
            get_class($this),
            $method
        ));
    }

    /**
     * Getter for $_responding
     *
     * @return bool
     */
    public function responding()
    {
        return $this->_responding;
    }

    /**
     * Enable the Crud action
     *
     * @return void
     */
    public function enable()
    {
        $this->setConfig('enabled', true);
    }

    /**
     * Test if a Crud action is enabled or not
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->getConfig('enabled');
    }

    /**
     * Disable the Crud action
     *
     * @return void
     */
    public function disable()
    {
        $this->setConfig('enabled', false);
    }

    /**
     * return the config for a given message type
     *
     * @param string $type Message type.
     * @param array $replacements Replacements
     * @return array
     * @throws \Exception for a missing or undefined message type
     */
    public function message($type, array $replacements = [])
    {
        if (empty($type)) {
            throw new \Exception('Missing message type');
        }

        $crud = $this->_crud();

        $config = $this->getConfig('messages.' . $type);
        if (empty($config)) {
            $config = $crud->getConfig('messages.' . $type);
            if (empty($config)) {
                throw new \Exception(sprintf('Invalid message type "%s"', $type));
            }
        }

        if (is_string($config)) {
            $config = ['text' => $config];
        }

        $config = Hash::merge([
            'element' => 'default',
            'params' => ['class' => 'message'],
            'key' => 'flash',
            'type' => $this->getConfig('action') . '.' . $type,
            'name' => $this->resourceName()
        ], $config);

        if (!isset($config['text'])) {
            throw new \Exception(sprintf('Invalid message config for "%s" no text key found', $type));
        }

        $config['params']['original'] = ucfirst(str_replace('{name}', $config['name'], $config['text']));

        $domain = $this->getConfig('messages.domain');
        if (!$domain) {
            $domain = $crud->getConfig('messages.domain') ?: 'crud';
        }

        $config['text'] = __d($domain, $config['params']['original']);

        $config['text'] = Text::insert(
            $config['text'],
            $replacements + ['name' => $config['name']],
            ['before' => '{', 'after' => '}']
        );

        $config['params']['class'] .= ' ' . $type;

        return $config;
    }

    /**
     * Wrapper for FlashComponent::set()
     *
     * @param string $type Message type
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     * @throws \Exception
     */
    public function setFlash($type, Subject $subject)
    {
        $subject->set($this->message($type));
        $event = $this->_trigger('setFlash', $subject);
        if ($event->isStopped()) {
            return;
        }

        $this->_controller()->Flash->set($subject->text, [
            'element' => $subject->element,
            'params' => $subject->params,
            'key' => $subject->key
        ]);
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
    public function redirectConfig($name = null, $config = null)
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
     * Get the action scope
     *
     * Usually it's 'table' or 'entity'
     *
     * @return string
     */
    public function scope()
    {
        return $this->getConfig('scope');
    }

    /**
     * Set "success" variable for view.
     *
     * @param \Cake\Event\Event $event Event
     * @return bool|null
     */
    public function publishSuccess(Event $event)
    {
        if (!isset($event->getSubject()->success)) {
            return false;
        }

        $this->_controller()->set('success', $event->getSubject()->success);
    }

    /**
     * Return the human name of the model
     *
     * By default it uses Inflector::humanize, but can be changed
     * using the "name" configuration property
     *
     * @param string|null $value Name to set
     * @return string
     */
    public function resourceName($value = null)
    {
        if ($value !== null) {
            return $this->setConfig('name', $value);
        }

        if (empty($this->_config['name'])) {
            $this->setConfig('name', $this->_deriveResourceName());
        }

        return $this->getConfig('name');
    }

    /**
     * Derive resource name
     *
     * @return string
     */
    protected function _deriveResourceName()
    {
        $inflectionType = $this->getConfig('inflection');

        if ($inflectionType === null) {
            $inflectionType = $this->scope() === 'entity' ? 'singular' : 'plural';
        }

        if ($inflectionType === 'singular') {
            return strtolower(Inflector::humanize(
                Inflector::singularize(Inflector::underscore($this->_table()->getAlias()))
            ));
        }

        return strtolower(Inflector::humanize(Inflector::underscore($this->_table()->getAlias())));
    }

    /**
     * Additional auxiliary events emitted if certain traits are loaded
     *
     * @return array
     */
    public function implementedEvents()
    {
        $events = parent::implementedEvents();

        $events['Crud.beforeRender'][] = ['callable' => [$this, 'publishSuccess']];

        if (method_exists($this, 'viewVar')) {
            $events['Crud.beforeRender'][] = ['callable' => [$this, 'publishViewVar']];
        }

        return $events;
    }

    /**
     * Get entity key
     *
     * @return string
     */
    public function subjectEntityKey()
    {
        $key = $this->getConfig('entityKey');
        if ($key !== null) {
            return $key;
        }

        return $this->scope() === 'entity' ? 'entity' : 'entities';
    }
}
