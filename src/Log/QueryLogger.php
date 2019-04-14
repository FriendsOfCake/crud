<?php
declare(strict_types=1);
namespace Crud\Log;

use Cake\Database\Log\LoggedQuery;

class QueryLogger extends \Cake\Database\Log\QueryLogger
{
    /**
     * Logs
     *
     * @var array
     */
    protected $_logs = [];

    /**
     * Get logs
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->_logs;
    }

    /**
     * Wrapper function for the logger object, useful for unit testing
     * or for overriding in subclasses.
     *
     * @param \Cake\Database\Log\LoggedQuery $query to be written in log
     * @return void
     */
    protected function _log(LoggedQuery $query): void
    {
        $this->_logs[] = $query;

        parent::_log($query);
    }
}
