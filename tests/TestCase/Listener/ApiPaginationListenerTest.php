<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use ArrayIterator;
use Cake\Controller\Controller;
use Cake\Datasource\Paging\PaginatedResultSet;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Crud\Action\BaseAction;
use Crud\Listener\ApiPaginationListener;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiPaginationListenerTest extends TestCase
{
    /**
     * Test implemented events
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_checkRequestType')
            ->willReturn(true);

        $result = $Instance->implementedEvents();
        $expected = [
            'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that API requests do not get processed
     * if there is no pagination data
     *
     * @return void
     */
    public function testBeforeRenderNoPaginationData()
    {
        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $Action = $this
            ->getMockBuilder(BaseAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request', '_action', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->willReturn($Action);
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->willReturn($Controller);

        $Instance->beforeRender(new Event('something'));

        $this->assertNull($Controller->viewBuilder()->getVar('pagination'));
    }

    /**
     * Test that API requests do not get processed
     * if there if the view var is not a PaginatedInterface instance.
     *
     * @return void
     */
    public function testBeforeRenderViewVarNotPaginatedInterface()
    {
        $Controller = new Controller(new ServerRequest(), 'MyModel');
        $Controller->set('data', []);

        $Action = $this
            ->getMockBuilder(BaseAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request', '_action', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->willReturn($Controller);
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->willReturn($Action);

        $Instance->beforeRender(new Event('something'));

        $this->assertNull($Controller->viewBuilder()->getVar('pagination'));
    }

    /**
     * Test that API requests do get processed
     * if there is pagination data
     *
     * @return void
     */
    public function testBeforeRenderWithPaginationData()
    {
        $Request = new ServerRequest();
        $paginatedResultset = new PaginatedResultSet(
            new ArrayIterator([]),
            [
                'pageCount' => 5,
                'currentPage' => 2,
                'hasNextPage' => true,
                'hasPrevPage' => true,
                'totalCount' => 50,
                'perPage' => 10,
                'count' => 10,
            ]
        );

        $Controller = new Controller($Request, 'MyModel');
        $Controller->set('data', $paginatedResultset);

        $Action = $this
            ->getMockBuilder(BaseAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setConfig'])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('setConfig')
            ->with('serialize.pagination', 'pagination');

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request', '_controller', '_action'])
            ->getMock();
        $Instance
            ->expects($this->any())
            ->method('_controller')
            ->willReturn($Controller);
        $Instance
            ->expects($this->any())
            ->method('_action')
            ->willReturn($Action);

        $Instance->beforeRender(new Event('something'));

        $expected = [
            'page_count' => 5,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'total_count' => 50,
            'count' => 10,
            'per_page' => 10,
        ];
        $this->assertEquals($expected, $Controller->viewBuilder()->getVar('pagination'));
    }
}
