<?php
declare(strict_types=1);

namespace Crud\Core;

use Cake\Controller\Controller;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\EventListenerInterface;

/**
 * Crud Base Class
 *
 * Implement base methods used in CrudAction and CrudListener classes
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseObject implements EventListenerInterface
{
    use InstanceConfigTrait;
    use ProxyTrait;

    /**
     * Container with reference to all objects
     * needed within the CrudListener and CrudAction
     *
     * @var \Cake\Controller\Controller
     */
    protected Controller $_controller;

    /**
     * Default configuration
     *
     * @var array
     */
    protected array $_defaultConfig = [];

    /**
     * Constructor
     *
     * @param \Cake\Controller\Controller $Controller Controller instance
     * @param array $config Default settings
     */
    public function __construct(Controller $Controller, array $config = [])
    {
        $this->_controller = $Controller;
        $this->setConfig($config);
    }

    /**
     * List of implemented events
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [];
    }

    /**
     * Convenient method for Request::is
     *
     * @param list<string>|string $method Method(s) to check for
     * @return bool
     */
    protected function _checkRequestType(array|string $method): bool
    {
        return $this->_request()->is($method);
    }
}
