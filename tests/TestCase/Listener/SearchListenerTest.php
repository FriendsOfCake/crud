<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Crud\Event\Subject;
use Crud\Listener\SearchListener;
use Crud\TestSuite\TestCase;
use Muffin\Webservice\Model\EndpointRegistry;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SearchListenerTest extends TestCase
{
    public function tearDown()
    {
        Plugin::unload('Search');
        TableRegistry::clear();
    }

    /**
     * Test implemented events
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\SearchListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $result = $listener->implementedEvents();
        $expected = [
            'Crud.beforeLookup' => ['callable' => 'injectSearch'],
            'Crud.beforePaginate' => ['callable' => 'injectSearch']
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test inject search exception
     *
     * @expectedException RuntimeException
     * @return void
     */
    public function testInjectSearchException()
    {
        Plugin::load('Search', ['path' => ROOT . DS]);

        $request = new ServerRequest();
        $response = new Response();
        $eventManager = new EventManager();
        $controller = new Controller($request, $response, 'Search', $eventManager);

        $behaviorRegistryMock = $this->getMockBuilder('\Cake\ORM\BehaviorRegistry')
            ->setMockClassName('BehaviorRegistry')
            ->getMock();
        $behaviorRegistryMock->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));

        $tableMock = $this->getMockBuilder('\Cake\ORM\Table')
            ->setMockClassName('SearchTables')
            ->setMethods(['behaviors', 'filterParams'])
            ->getMock();
        $tableMock->expects($this->any())
            ->method('behaviors')
            ->will($this->returnValue($behaviorRegistryMock));

        TableRegistry::set('Search', $tableMock);

        $queryMock = $this->getMockBuilder('\Cake\ORM\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject();
        $subject->query = $queryMock;

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup'
            ]
        ]);

        $listener->injectSearch(new Event('Crud.beforePaginate', $subject));
    }

    /**
     * Test inject search
     *
     * @return void
     */
    public function testInjectSearch()
    {
        Plugin::load('Search', ['path' => ROOT . DS]);

        $params = [
            'search' => [
                'name' => '1st post',
            ],
            'collection' => 'search'
        ];

        $request = (new ServerRequest())->withQueryParams($params['search']);

        $response = new Response();
        $eventManager = new EventManager();
        $controller = new Controller($request, $response, 'Search', $eventManager);

        $behaviorRegistryMock = $this->getMockBuilder('\Cake\ORM\BehaviorRegistry')
            ->setMockClassName('BehaviorRegistry')
            ->setMethods(['hasMethod', 'has'])
            ->getMock();
        $behaviorRegistryMock->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $behaviorRegistryMock->expects($this->once())
            ->method('hasMethod')
            ->will($this->returnValue(false));

        $tableMock = $this->getMockBuilder('\Cake\ORM\Table')
            ->setMockClassName('SearchTables')
            ->setMethods(['behaviors'])
            ->getMock();
        $tableMock->expects($this->any())
            ->method('behaviors')
            ->will($this->returnValue($behaviorRegistryMock));

        TableRegistry::set('Search', $tableMock);

        $queryMock = $this->getMockBuilder('\Cake\ORM\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('find')
            ->with('search', $params)
            ->will($this->returnValue($queryMock));

        $subject = new Subject();
        $subject->query = $queryMock;

        $event = new Event('Crud.beforeLookup', $subject);

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup'
            ],
            'collection' => 'search'
        ]);
        $listener->injectSearch($event);
    }

    /**
     * Test inject search
     *
     * @return void
     */
    public function testInjectSearchWebserviceEndpoint()
    {
        Plugin::load('Search', ['path' => ROOT . DS]);
        Plugin::load('Muffin/Webservice', ['path' => ROOT . '/vendor/muffin/webservice/']);

        $params = [
            'search' => [
                'name' => '1st post',
            ],
            'collection' => 'search'
        ];

        $request = new ServerRequest(['query' => $params['search']]);

        $response = new Response();
        $eventManager = new EventManager();
        $controller = new Controller($request, $response, 'Search', $eventManager);
        $controller->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);
        $controller->setModelType('Endpoint');

        $queryMock = $this->getMockBuilder('\Muffin\Webservice\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('find')
            ->with('search', $params)
            ->will($this->returnValue($queryMock));

        $subject = new Subject();
        $subject->query = $queryMock;

        $event = new Event('Crud.beforeLookup', $subject);

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup'
            ],
            'collection' => 'search'
        ]);
        $listener->injectSearch($event);
    }
}
