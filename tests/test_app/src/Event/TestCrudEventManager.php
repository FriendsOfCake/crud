<?php
declare(strict_types=1);

namespace Crud\Test\App\Event;

use Cake\Event\EventInterface;
use Cake\Event\EventManager;

/**
 * TestCrudEventManager
 *
 * This manager class is used to replace the EventManger instance.
 * As such, it becomes a global listener and is used to keep a log of
 * all events fired during the test
 */
class TestCrudEventManager extends EventManager
{
    protected $log = [];

    public function dispatch($event): EventInterface
    {
        $this->log[] = [
            'name' => $event->getName(),
            'subject' => $event->getSubject(),
        ];

        return parent::dispatch($event);
    }

    public function getLog($params = [])
    {
        $params += ['clear' => true, 'format' => 'names'];

        $log = $this->log;

        if ($params['format'] === 'names') {
            $return = [];
            foreach ($log as $entry) {
                $return[] = $entry['name'];
            }
            $log = $return;
        }

        if ($params['clear']) {
            $this->log = [];
        }

        return $log;
    }
}
