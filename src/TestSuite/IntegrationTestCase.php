<?php
declare(strict_types=1);

namespace Crud\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Crud\TestSuite\Traits\CrudTestTrait;
use FriendsOfCake\TestUtilities\AccessibilityHelperTrait;
use FriendsOfCake\TestUtilities\CounterHelperTrait;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class IntegrationTestCase extends TestCase
{
    use AccessibilityHelperTrait;
    use CounterHelperTrait;
    use CrudTestTrait;
    use IntegrationTestTrait;

    /**
     * @var \Cake\Event\EventManagerInterface
     */
    protected $_eventManager;

    /**
     * [setUp description]
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->resetReflectionCache();

        $this->_eventManager = EventManager::instance();

        $existing = Configure::read('App.paths.templates');
        $existing[] = Plugin::path('Crud') . 'tests/test_app/templates/';
        Configure::write('App.paths.templates', $existing);

        Configure::write('App.namespace', 'Crud\Test\App');

        Router::extensions('json');

        $routeBuilder = Router::createRouteBuilder('/');

        $routeBuilder->connect('/{controller}', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
        $routeBuilder->connect('/{controller}/{action}/*', [], ['routeClass' => 'DashedRoute']);
    }
}
