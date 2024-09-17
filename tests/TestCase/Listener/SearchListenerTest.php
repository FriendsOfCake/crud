<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\BehaviorRegistry;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Crud\Event\Subject;
use Crud\Listener\SearchListener;
use Crud\TestSuite\TestCase;
use Muffin\Webservice\Model\EndpointRegistry;
use RuntimeException;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SearchListenerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->skipIf(!class_exists('\Search\SearchPlugin'), 'Search plugin is not loaded');
    }

    public function tearDown(): void
    {
        $this->removePlugins(['Search']);

        $this->getTableLocator()->clear();
    }

    /**
     * Test implemented events
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $listener = $this
            ->getMockBuilder(SearchListener::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $result = $listener->implementedEvents();
        $expected = [
            'Crud.beforeLookup' => ['callable' => 'injectSearch'],
            'Crud.beforePaginate' => ['callable' => 'injectSearch'],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test inject search exception
     *
     * @return void
     */
    public function testInjectSearchException()
    {
        $this->expectException(RuntimeException::class);

        $request = new ServerRequest();
        $eventManager = new EventManager();
        $controller = new Controller($request, 'Search', $eventManager);
        $controller->loadComponent('Crud');

        $behaviorRegistryMock = $this->getMockBuilder(BehaviorRegistry::class)
            ->setMockClassName('BehaviorRegistry')
            ->getMock();
        $behaviorRegistryMock->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $tableMock = $this->getMockBuilder(Table::class)
            ->setMockClassName('SearchesTable')
            ->onlyMethods(['behaviors'])
            ->getMock();
        $tableMock->expects($this->any())
            ->method('behaviors')
            ->willReturn($behaviorRegistryMock);

        $this->getTableLocator()->set('Search', $tableMock);

        $queryMock = $this->getMockBuilder(SelectQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject = new Subject();
        $subject->query = $queryMock;

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup',
            ],
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
        $this->loadPlugins(['Search']);

        $params = [
            'search' => [
                'name' => '1st post',
            ],
            'collection' => 'search',
        ];

        $request = (new ServerRequest())
            ->withParam('action', 'index')
            ->withQueryParams($params['search']);

        $eventManager = new EventManager();
        $controller = new Controller($request, 'Search', $eventManager);
        $controller->loadComponent('Crud.Crud');

        $behaviorRegistryMock = $this->getMockBuilder(BehaviorRegistry::class)
            ->setMockClassName('BehaviorRegistry')
            ->onlyMethods(['has'])
            ->getMock();
        $behaviorRegistryMock->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $tableMock = $this->getMockBuilder(Table::class)
            ->setMockClassName('SearchTables')
            ->onlyMethods(['behaviors'])
            ->getMock();
        $tableMock->expects($this->any())
            ->method('behaviors')
            ->willReturn($behaviorRegistryMock);

        $this->getTableLocator()->set('Search', $tableMock);

        $queryMock = new class {
            public TestCase $testCase;

            public function find($query, $search, $collection)
            {
                $this->testCase->assertSame(['name' => '1st post'], $search);
                $this->testCase->assertSame('search', $collection);
            }
        };

        $queryMock->testCase = $this;

        $subject = new Subject();
        $subject->query = $queryMock;

        $event = new Event('Crud.beforeLookup', $subject);

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup',
            ],
            'collection' => 'search',
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
        if (!class_exists(EndpointRegistry::class)) {
            $this->markTestSkipped(
                'Muffin/Webservice plugin is not loaded.'
            );
        }

        $this->loadPlugins(['Search', 'Muffin/Webservice']);

        $params = [
            'search' => [
                'name' => '1st post',
            ],
            'collection' => 'search',
        ];

        $request = new ServerRequest(['query' => $params['search']]);

        $response = new Response();
        $eventManager = new EventManager();
        $controller = new Controller($request, $response, 'Search', $eventManager);
        $controller->modelFactory('Endpoint', ['Muffin\Webservice\Model\EndpointRegistry', 'get']);
        $controller->setModelType('Endpoint');

        $queryMock = $this->getMockBuilder(SelectQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('find')
            ->with('search', $params)
            ->willReturn($queryMock);

        $subject = new Subject();
        $subject->query = $queryMock;

        $event = new Event('Crud.beforeLookup', $subject);

        $listener = new SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup',
            ],
            'collection' => 'search',
        ]);
        $listener->injectSearch($event);
    }
}
