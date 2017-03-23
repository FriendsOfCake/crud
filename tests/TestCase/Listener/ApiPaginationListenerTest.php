<?php
namespace Crud\Test\TestCase\Listener;

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
        // assert success
        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->any())
            ->method('_checkRequestType')
            ->will($this->returnValue(true));

        $result = $Instance->implementedEvents();
        $expected = [
            'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75]
        ];
        $this->assertEquals($expected, $result);

        // assert null for non-api and non-jsonapi requests
        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->any())
            ->method('_checkRequestType')
            ->will($this->returnValue(false));

        $this->assertNull($Instance->implementedEvents());
    }

    /**
     * Test API requests do not get processed if there is no pagination data.
     *
     * @return void
     */
    public function testBeforeRenderNoPaginationData()
    {
        $Request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();

        $Controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($Controller));

        $Request->paging = ['MyModel' => []];
        $Controller->modelClass = 'MyModel';

        $Instance->beforeRender(new \Cake\Event\Event('something'));
    }

    /**
     * Test API requests do not get processed if pagination data is NULL.
     *
     * @return void
     */
    public function testBeforeRenderPaginationDataIsNull()
    {
        $Request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->never())
            ->method('_controller');

        $Request->paging = null;

        $Instance->beforeRender(new \Cake\Event\Event('something'));
    }

    /**
     * Make sure API requests do not get processed if pagination data is
     * present nut does not apply to the current controller.
     *
     * @return void
     */
    public function testBeforeRenderPaginationDataDoesNotMatchController()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $controller->modelClass = 'NonMatchingControllers';

        $request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller'])
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($request));
        $listener
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $request->paging = [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10
            ]
        ];

        $this->assertNull($listener->beforeRender(new \Cake\Event\Event('something')));
    }

    /**
     * Test ApiListener requests do get processed if there is pagination data.
     *
     * @return void
     */
    public function testBeforeRenderWithApiPaginationData()
    {
        $Request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();
        $Request->paging = [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10
            ]
        ];

        $expected = [
            'page_count' => 10,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'count' => 100,
            'limit' => 10
        ];

        $Controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('pagination', $expected);

        $Action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('config')
            ->with('serialize.pagination', 'pagination');

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller', '_action'])
            ->getMock();
        $Instance
            ->expects($this->any())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));

        $Controller->modelClass = 'MyModel';

        $Instance->beforeRender(new \Cake\Event\Event('something'));
    }

    /**
     * Test JsonApiListener requests do get processed if there is pagination
     * data.
     *
     * @return void
     */
    public function testBeforeRenderWithJsonApiPaginationData()
    {
        $request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();
        $request->paging = [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => 3,
                'prevPage' => 1,
                'count' => 100,
                'limit' => 10
            ]
        ];

        $expected = [
            'self' => '/countries?page=2',
            'first' => '/countries?page=1',
            'last' => '/countries?page=10',
            'prev' => '/countries?page=1',
            'next' => '/countries?page=3',
            'record_count' => 100,
            'page_count' => 10,
            'page_limit' => 10,
        ];

        $crud = $this
            ->getMockBuilder('\Crud\Controller\Component\CrudComponent')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();

        $crud
            ->expects($this->at(0))
            ->method('config')
            ->will($this->returnValue(false)); // assert relative links

        $crud
            ->expects($this->at(1))
            ->method('config')
            ->will($this->returnValue(true)); // assert absolute links

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $controller->Crud = $crud;
        $controller->name = 'countries';

        $controller
            ->expects($this->any())
            ->method('set')
            ->with('_pagination', $expected);

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller', '_action', '_checkRequestType', 'config'])
            ->getMock();
        $listener
            ->expects($this->any())
            ->method('_request')
            ->will($this->returnValue($request));
        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));
        $listener
            ->expects($this->any())
            ->method('_checkRequestType')
            ->will($this->returnValue(true));

        $controller->modelClass = 'MyModel';

        $listener->beforeRender(new \Cake\Event\Event('something')); // assert relative links
        $listener->beforeRender(new \Cake\Event\Event('something')); // assert absolute links
    }

    /**
     * Test with pagination data for plugin model.
     *
     * @return void
     */
    public function testBeforeRenderWithPaginationDataForPluginModel()
    {
        $Request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->getMock();
        $Request->paging = [
            'MyModel' => [
                'pageCount' => 10,
                'page' => 2,
                'nextPage' => true,
                'prevPage' => true,
                'count' => 100,
                'limit' => 10
            ]
        ];

        $expected = [
            'page_count' => 10,
            'current_page' => 2,
            'has_next_page' => true,
            'has_prev_page' => true,
            'count' => 100,
            'limit' => 10
        ];

        $Controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('pagination', $expected);

        $Action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('config')
            ->with('serialize.pagination', 'pagination');

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_request', '_controller', '_action'])
            ->getMock();
        $Instance
            ->expects($this->any())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Instance
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));

        $Controller->modelClass = 'MyPlugin.MyModel';

        $Instance->beforeRender(new \Cake\Event\Event('something'));
    }

    /**
     * Test generating viewVars with JSON API compatible pagination links.
     *
     * @return void
     */
    public function testGetJsonApiPaginationViewVars()
    {
        $crud = $this
            ->getMockBuilder('\Crud\Controller\Component\CrudComponent')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();

        $crud
            ->expects($this->any())
            ->method('config')
            ->will($this->returnValue(false));

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(['name'])
            ->getMock();

        $controller->Crud = $crud;
        $controller->name = 'countries';

        $listener = $this
            ->getMockBuilder('\Crud\Listener\ApiPaginationListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller'])
            ->getMock();
        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $this->setReflectionClassInstance($listener);

        // assert page 1-out-of-1
        $pagination = [
            'page' => 1, // self
            'pageCount' => 1, // last
            'prevPage' => false,
            'nextPage' => false,
            'count' => 8, // record_count
            'limit' => 10 // page_limit
        ];

        $expected = [
            'self' => '/countries?page=1',
            'first' => '/countries?page=1',
            'last' => '/countries?page=1',
            'prev' => null,
            'next' => null,
            'record_count' => 8,
            'page_count' => 1,
            'page_limit' => 10,
        ];

        $result = $this->callProtectedMethod('_getJsonApiPaginationResponse', [$pagination], $listener);
        $this->assertSame($expected, $result);

        // assert page 1-out-of-3
        $pagination = [
            'page' => 1,
            'pageCount' => 3,
            'prevPage' => false,
            'nextPage' => true,
            'count' => 28,
            'limit' => 10
        ];

        $expected = [
            'self' => '/countries?page=1',
            'first' => '/countries?page=1',
            'last' => '/countries?page=3',
            'prev' => null,
            'next' => '/countries?page=2',
            'record_count' => 28,
            'page_count' => 3,
            'page_limit' => 10,
        ];

        $result = $this->callProtectedMethod('_getJsonApiPaginationResponse', [$pagination], $listener);
        $this->assertSame($expected, $result);

        // assert page 2-out-of-3
        $pagination = [
            'page' => 2,
            'pageCount' => 3,
            'prevPage' => true,
            'nextPage' => true,
            'count' => 28,
            'limit' => 10
        ];

        $expected = [
            'self' => '/countries?page=2',
            'first' => '/countries?page=1',
            'last' => '/countries?page=3',
            'prev' => '/countries?page=1',
            'next' => '/countries?page=3',
            'record_count' => 28,
            'page_count' => 3,
            'page_limit' => 10,
        ];

        $result = $this->callProtectedMethod('_getJsonApiPaginationResponse', [$pagination], $listener);
        $this->assertSame($expected, $result);

        // assert page 3-out-of-3
        $pagination = [
            'page' => 3,
            'pageCount' => 3,
            'prevPage' => true,
            'nextPage' => false,
            'count' => 28,
            'limit' => 10
        ];

        $expected = [
            'self' => '/countries?page=3',
            'first' => '/countries?page=1',
            'last' => '/countries?page=3',
            'prev' => '/countries?page=2',
            'next' => null,
            'record_count' => 28,
            'page_count' => 3,
            'page_limit' => 10,
        ];

        $result = $this->callProtectedMethod('_getJsonApiPaginationResponse', [$pagination], $listener);
        $this->assertSame($expected, $result);
    }
}
