<?php
namespace Crud\Test\TestCase\Action;

use Cake\Core\Plugin;
use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexActionTest extends IntegrationTestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = ['plugin.Crud.Blogs'];

    /**
     * setUp()
     *
     * @return void
     */
    public function setUp()
    {
        $this->deprecated(function () {
            Plugin::load('Crud', ['path' => ROOT . DS, 'autoload' => true]);
        });

        parent::setUp();

        $this->useHttpServer(true);
    }

    /**
     * Data provider with all HTTP verbs
     *
     * @return array
     */
    public function allHttpMethodProvider()
    {
        return [
            ['get'],
            ['post'],
            ['put'],
            ['delete'],
        ];
    }

    /**
     * Test the normal HTTP flow for all HTTP verbs
     *
     * @dataProvider allHttpMethodProvider
     * @return void
     */
    public function testGet($method)
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->{$method}('/blogs');
        $this->assertContains(
            'Page 1 of 2, showing 3 records out of 5 total',
            (string)$this->_response->getBody()
        );
        $this->assertEvents(['beforePaginate', 'afterPaginate', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blogs'));
        $this->assertNotNull($this->viewVariable('success'));
    }

    /**
     * Test that changing the viewVar reflects in controller::$viewVar
     *
     * @return void
     */
    public function testGetWithViewVar()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Crud->action('index')->viewVar('items');
                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->get('/blogs');

        $this->assertContains(
            'Page 1 of 2, showing 3 records out of 5 total',
            (string)$this->_response->getBody()
        );
        $this->assertEvents(['beforePaginate', 'afterPaginate', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('items'));
        $this->assertNotNull($this->viewVariable('success'));
    }

    /**
     * Tests that it is posisble to modify the pagination query in beforePaginate
     *
     * @return void
     */
    public function testModifyQueryInEvent()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function () {
                $this->_controller->Crud->on('beforePaginate', function ($event) {
                    $event->getSubject()->query->where(['id <' => 2]);
                });
            }
        );

        $this->get('/blogs');
        $this->assertContains(
            'Page 1 of 1, showing 1 records out of 1 total',
            (string)$this->_response->getBody()
        );
    }

    /**
     * Test using custom finder with options.
     *
     * @return void
     */
    public function testGetWithCustomFinder()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function () {
                $this->_controller->Crud->action('index')
                    ->findMethod(['withCustomOptions' => ['foo' => 'bar']]);
            }
        );

        $this->get('/blogs');
        $this->assertSame(['foo' => 'bar'], $this->_controller->Blogs->customOptions);
    }
}
