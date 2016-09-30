<?php
namespace Crud\Traits;

use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;

trait QueryLogTrait
{
    /**
     * Setup logging for all connections
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function setupLogging(Event $event)
    {
        foreach ($this->_getSources() as $connectionName) {
            try {
                $this->_getSource($connectionName)->logQueries(true);
                $this->_getSource($connectionName)->logger(new \Crud\Log\QueryLogger());
            } catch (\Cake\Datasource\Exception\MissingDatasourceConfigException $e) {
                //Safe to ignore this :-)
            }
        }
    }

    /**
     * Get the query logs for all sources
     *
     * @return array
     */
    protected function _getQueryLogs()
    {
        $sources = $this->_getSources();

        $queryLog = [];
        foreach ($sources as $source) {
            $logger = $this->_getSource($source)->logger();
            if (method_exists($logger, 'getLogs')) {
                $queryLog[$source] = $logger->getLogs();
            }
        }

        return $queryLog;
    }

    /**
     * Get a list of sources defined in database.php
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function _getSources()
    {
        return ConnectionManager::configured();
    }

    /**
     * Get a specific data source
     *
     * @param string $source Datasource name
     * @return \Cake\Database\Connection
     * @codeCoverageIgnore
     */
    protected function _getSource($source)
    {
        return ConnectionManager::get($source);
    }
}
