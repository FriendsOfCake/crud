<?php
declare(strict_types=1);

namespace Crud\Log;

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
    public function getLogs(): array
    {
        return $this->_logs;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        $this->_logs[] = (string)$context['query'];

        parent::log($level, $message, $context);
    }
}
