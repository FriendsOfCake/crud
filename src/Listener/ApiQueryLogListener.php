<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\MissingDatasourceConfigException;
use Cake\Event\EventInterface;
use Crud\Log\QueryLogger;
use Crud\Traits\QueryLogTrait;

/**
 * When loaded Crud API will include query logs in the response
 *
 * Very much like the DebugKit version, the SQL log will only be appended
 * if the following conditions is true:
 *  1) The request must be 'api' (.json/.xml) or 'jsonapi'
 *  2) The debug level must be 2 or above
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLogListener extends BaseListener
{
    use QueryLogTrait;

    /**
     * {@inheritDoc}
     *
     * `connections` List of connection names to log. Empty means all defined connections.
     */
    protected array $_defaultConfig = [
        'connections' => [],
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * We attach at priority 10 so normal bound events can run before us
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        if (!$this->_checkRequestType('api')) {
            return [];
        }

        return [
            'Crud.beforeFilter' => ['callable' => [$this, 'setupLogging'], 'priority' => 1],
            'Crud.beforeRender' => ['callable' => [$this, 'beforeRender'], 'priority' => 75],
        ];
    }

    /**
     * Setup logging for all connections
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return void
     */
    public function setupLogging(EventInterface $event): void
    {
        $connections = $this->getConfig('connections') ?: ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            try {
                $driver = ConnectionManager::get($connectionName)->getDriver();
                if (method_exists($driver, 'setLogger')) {
                    $driver->setLogger(new QueryLogger());
                }
            } catch (MissingDatasourceConfigException $e) {
                //Safe to ignore this :-)
            }
        }
    }

    /**
     * Appends the query log to the JSON or XML output
     *
     * @param \Cake\Event\EventInterface<\Crud\Event\Subject> $event Event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        if (!Configure::read('debug')) {
            return;
        }

        $this->_action()->setConfig('serialize.queryLog', 'queryLog');
        $this->_controller()->set('queryLog', $this->getQueryLogs());
    }

    /**
     * Public getter to expose logs for use in other (exception) classes.
     *
     * @return array
     */
    public function getQueryLogs(): array
    {
        return $this->_getQueryLog();
    }
}
