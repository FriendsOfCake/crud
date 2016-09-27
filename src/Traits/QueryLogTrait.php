<?php
namespace Crud\Traits;

use Cake\Datasource\ConnectionManager;

trait QueryLogTrait
{

    /**
     * Helper method to get query log.
     *
     * @return array Query log.
     */
    protected function _getQueryLog()
    {
        $queryLog = [];
        $sources = ConnectionManager::configured();
        foreach ($sources as $source) {
            $logger = ConnectionManager::get($source)->logger();
            if (method_exists($logger, 'getLogs')) {
                $queryLog[$source] = $logger->getLogs();
            }
        }

        return $queryLog;
    }
}
