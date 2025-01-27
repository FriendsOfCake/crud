<?php
declare(strict_types=1);

namespace Crud\Traits;

use Cake\Datasource\ConnectionManager;

trait QueryLogTrait
{
    /**
     * Get the query logs
     *
     * @return array
     */
    protected function _getQueryLog(): array
    {
        $queryLog = [];

        foreach (ConnectionManager::configured() as $source) {
            $driver = ConnectionManager::get($source)->getDriver();
            if (!method_exists($driver, 'getLogger')) {
                continue;
            }

            $logger = $driver->getLogger();
            if ($logger && method_exists($logger, 'getLogs')) {
                /** @var \Crud\Log\QueryLogger $logger */
                $queryLog[$source] = $logger->getLogs();
            }
        }

        return $queryLog;
    }
}
