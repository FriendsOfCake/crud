<?php
declare(strict_types=1);

namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\MissingDatasourceConfigException;
use Cake\Event\EventInterface;
use Crud\Log\QueryLogger;

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
    /**
     * {@inheritDoc}
     *
     * `connections` List of connection names to log. Empty means all defined connections.
     */
    protected $_defaultConfig = [
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
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function setupLogging(EventInterface $event): void
    {
        $connections = $this->getConfig('connections') ?: $this->_getSources();

        foreach ($connections as $connectionName) {
            try {
                $connection = $this->_getSource($connectionName);
                $connection->enableQueryLogging(true);
                /** @psalm-suppress InternalMethod */
                $connection->setLogger(new QueryLogger());
            } catch (MissingDatasourceConfigException $e) {
                //Safe to ignore this :-)
            }
        }
    }

    /**
     * Appends the query log to the JSON or XML output
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        if (!Configure::read('debug')) {
            return;
        }

        $this->_action()->setConfig('serialize.queryLog', 'queryLog');
        $this->_controller()->set('queryLog', $this->_getQueryLogs());
    }

    /**
     * Get the query logs for all sources
     *
     * @return array
     */
    protected function _getQueryLogs(): array
    {
        $sources = $this->_getSources();

        $queryLog = [];
        foreach ($sources as $source) {
            $logger = $this->_getSource($source)->getLogger();
            if (method_exists($logger, 'getLogs')) {
                $queryLog[$source] = $logger->getLogs();
            }
        }

        return $queryLog;
    }

    /**
     * Public getter to expose logs for use in other (exception) classes.
     *
     * @return array
     */
    public function getQueryLogs(): array
    {
        return $this->_getQueryLogs();
    }

    /**
     * Get a list of sources defined in database.php
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function _getSources(): array
    {
        return ConnectionManager::configured();
    }

    /**
     * Get a specific data source
     *
     * @param string $source Datasource name
     * @return \Cake\Datasource\ConnectionInterface
     * @codeCoverageIgnore
     */
    protected function _getSource(string $source)
    {
        return ConnectionManager::get($source);
    }
}
