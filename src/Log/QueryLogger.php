<?php
declare(strict_types=1);

namespace Crud\Log;

use Cake\Database\Log\LoggedQuery;
use Cake\Database\Log\QueryLogger as CakeQueryLogger;
use Stringable;

class QueryLogger extends CakeQueryLogger
{
    /**
     * Logs
     *
     * @var array
     */
    protected array $_logs = [];

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
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if ($context['query'] instanceof LoggedQuery) {
            $this->_logs[] = $context['query']->jsonSerialize();
        } else {
            $this->_logs[] = (string)$context['query'];
        }

        parent::log($level, $message, $context);
    }
}
