<?php
namespace Crud\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Routing\Router;
use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class IntegrationTestCase extends \Cake\TestSuite\IntegrationTestCase
{

    use AccessibilityHelperTrait;
    use CounterHelperTrait;
    use CrudTestTrait;

    /**
     * [setUp description]
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->resetReflectionCache();

        $this->_eventManager = EventManager::instance();

        $existing = Configure::read('App.paths.templates');
        $existing[] = Plugin::path('Crud') . 'tests/App/Template/';
        Configure::write('App.paths.templates', $existing);

        Configure::write('App.namespace', 'Crud\Test\App');

        Router::extensions('json');

        Router::connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
        Router::connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
        $this->useHttpServer(false);
    }

    /**
     * Helper method for check deprecation methods
     *
     * @param callable $callable callable function that will receive asserts
     * @return void
     */
    public function deprecated($callable)
    {
        $errorLevel = error_reporting();
        error_reporting(E_ALL ^ E_USER_DEPRECATED);
        try {
            $callable();
        } finally {
            error_reporting($errorLevel);
        }
    }
}
