<?php
declare(strict_types=1);

namespace Crud\TestSuite\Traits;

use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\Table;
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
    protected $_subject;

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
            /** @psalm-suppress UndefinedThisPropertyFetch */
            $controller = $this->controller;
        }

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $controller->Crud->on('beforeRender', function ($event): void {
            $this->_subject = $event->getSubject();
        });

        $controller->Crud->on('beforeRedirect', function ($event): void {
            $this->_subject = $event->getSubject();
        });
    }

    /**
     * Get a "model" (Table) instance
     *
     * @param string $class Full table class name
     * @param mixed $methods Methods to mock
     * @param string $alias Table alias / name
     * @param string $table Table name in the database
     * @return \Cake\ORM\Table
     * @psalm-param class-string $class
     * @psalm-suppress InvalidReturnType
     */
    public function getModel(string $class, $methods, string $alias, string $table): Table
    {
        /** @psalm-suppress DeprecatedMethod */
        $mock = $this->getMockBuilder($class)
            ->setMethods($methods)
            ->setConstructorArgs([['alias' => $alias, 'table' => $table]])
            ->getMock();
        /** @psalm-suppress UndefinedInterfaceMethod */
        $mock->setConnection(ConnectionManager::get('test'));

        return $mock;
    }

    /**
     * Assert these CRUD events was emitted during the life cycle
     *
     * The `$expected` list do not need to prefix events with `Crud.` - this is done
     * automatically before comparison
     *
     * @param array $expected An array of CRUD events we expected to be fired
     * @param array|null $actual Can be an Event class, Crud subject or array with event names
     * @return void
     * @throws \Exception
     */
    public function assertEvents(array $expected, ?array $actual = null): void
    {
        if ($actual === null) {
            $actual = $this->_subject;
        }

        if ($actual instanceof Event) {
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
