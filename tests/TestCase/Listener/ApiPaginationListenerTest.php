<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
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
            ->will($this->returnValue(true));

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
        $Request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods([])
            ->getMock();
        $Request = $Request->withAttribute('paging', ['MyModel' => []]);

        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));

        $Controller->modelClass = 'MyModel';

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test that API requests do not get processed
     * if there if pagination data is NULL
     *
     * @return void
     */
    public function testBeforeRenderPaginationDataIsNull()
    {
        $Request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods([])
            ->getMock();

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->never())
            ->method('_controller');

        $Request = $Request->withAttribute('paging', null);

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test that API requests do get processed
     * if there is pagination data
     *
     * @return void
     */
    public function testBeforeRenderWithPaginationData()
    {
        $Request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods([])
            ->getMock();
        $Request = $Request->withAttribute('paging', [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10,
            ],
        ]);

        $expected = [
            'page_count' => 10,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'count' => 100,
            'limit' => 10,
        ];

        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('pagination', $expected);

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
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));

        $Controller->modelClass = 'MyModel';

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test with pagination data for plugin model
     *
     * @return void
     */
    public function testBeforeRenderWithPaginationDataForPluginModel()
    {
        $Request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods([])
            ->getMock();
        $Request = $Request->withAttribute('paging', [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10,
            ],
        ]);

        $expected = [
            'page_count' => 10,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'count' => 100,
            'limit' => 10,
        ];

        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('pagination', $expected);

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
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));

        $Controller->modelClass = 'MyPlugin.MyModel';

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test if the pagination is set to be serialized in the beforeRender event
     *
     * @return void
     */
    public function testBeforeRenderMakeSurePaginationDataIsSetToBeSerialized()
    {
        $Request = $this
            ->getMockBuilder(ServerRequest::class)
            ->onlyMethods([])
            ->getMock();
        $Request = $Request->withAttribute('paging', [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10,
            ],
        ]);

        $expected = [
            'page_count' => 10,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'count' => 100,
            'limit' => 10,
        ];

        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('pagination', $expected);

        $Action = $this
            ->getMockBuilder(BaseAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $Instance = $this
            ->getMockBuilder(ApiPaginationListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_request', '_controller', '_action'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));

        $Controller->modelClass = 'MyModel';

        $Instance->beforeRender(new Event('something'));

        $this->assertSame('pagination', $Action->getConfig('serialize.pagination'));
    }
}
