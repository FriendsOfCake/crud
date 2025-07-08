<?php
declare(strict_types=1);

namespace Crud\TestSuite\Traits;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Crud\Event\Subject;
use Exception;

/**
 * Utility methods for easier testing with Crud in CakePHP & PHPUnit
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
trait CrudTestTrait
{
    /**
     * Reference to the final CRUD event subject after full event cycle
     *
     * @var \Crud\Event\Subject
     */
    protected Subject $_subject;

    /**
     * Subscribe to Crud.beforeRender and Crud.beforeRedirect events
     *
     * This is the two 'final' emitted events after a CRUD life cycle,
     * and thus will hold the final object
     *
     * It's stored in the `$this->_subject` property
     *
     * @param \Cake\Controller\Controller|null $controller Controller
     * @return void
     */
    protected function _subscribeToEvents(?Controller $controller = null): void
    {
        if ($controller === null) {
            $controller = $this->controller;
        }

        $controller->Crud->on('beforeRender', function ($event): void {
            $this->_subject = $event->getSubject();
        });

        $controller->Crud->on('beforeRedirect', function ($event): void {
            $this->_subject = $event->getSubject();
        });
    }

    /**
     * Assert these CRUD events was emitted during the life cycle
     *
     * The `$expected` list do not need to prefix events with `Crud.` - this is done
     * automatically before comparison
     *
     * @param array $expected An array of CRUD events we expected to be fired
     * @param \Cake\Event\EventInterface|array|null $actual Can be an Event class, Crud subject or array with event names
     * @return void
     * @throws \Exception
     */
    public function assertEvents(array $expected, EventInterface|array|null $actual = null): void
    {
        if ($actual === null) {
            $actual = $this->_subject;
        }

        if ($actual instanceof EventInterface) {
            $actual = $actual->getSubject()->getEvents();
        }

        if ($actual instanceof Subject) {
            $actual = $actual->getEvents();
        }

        if (empty($actual)) {
            throw new Exception('assertEvents: Expected actual to be not-empty');
        }

        if (!is_array($actual)) {
            throw new Exception('assertEvents: Expected actual to be an array');
        }

        foreach ($expected as &$key) {
            if (strpos($key, '.') !== false) {
                continue;
            }

            $key = 'Crud.' . $key;
        }

        $this->assertEquals($expected, $actual, 'Not all expected events was fired');
    }
}
