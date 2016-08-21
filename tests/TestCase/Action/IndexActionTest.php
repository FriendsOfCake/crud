<?php
namespace Crud\Test\TestCase\Action;

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
    public $fixtures = ['plugin.crud.blogs'];

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
            ['delete']
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
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->{$method}('/blogs');
        $this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $this->_response->body());
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
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Crud->action('index')->viewVar('items');
                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->get('/blogs');

        $this->assertContains('Page 1 of 2, showing 3 records out of 5 total', $this->_response->body());
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
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function () {
                $this->_controller->Crud->on('beforePaginate', function ($event) {
                    $event->subject->query->where(['id <' => 2]);
                });
            }
        );

        $this->get('/blogs');
        $this->assertContains('Page 1 of 1, showing 1 records out of 1 total', $this->_response->body());
    }
}
