<?php
namespace Crud\Test\TestCase\Listener;

use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SearchListenerTest extends TestCase
{

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
        $listener = $this
            ->getMockBuilder('\Crud\Listener\SearchListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $subject = new \Crud\Event\Subject();

        $listener->injectSearch(new \Cake\Event\Event('Crud.beforePaginate', $subject));
    }

    /**
     * Test inject search
     *
     * @return void
     */
    public function testInjectSearch()
    {
        \Cake\Core\Plugin::load('Search', ['path' => ROOT . DS]);

        $params = [
            'search' => [
                'name' => '1st post',
            ],
        ];

        $request = new \Cake\Network\Request();
        $response = new \Cake\Network\Response();
        $eventManager = new \Cake\Event\EventManager();
        $controller = new \Cake\Controller\Controller($request, $response, 'Search', $eventManager);

        $behaviorRegistryMock = $this->getMockBuilder('\Cake\ORM\BehaviorRegistry')
            ->setMockClassName('BehaviorRegistry')
            ->setMethods(['hasMethod'])
            ->getMock();
        $behaviorRegistryMock->expects($this->once())
            ->method('hasMethod')
            ->will($this->returnValue(true));

        $tableMock = $this->getMockBuilder('\Cake\ORM\Table')
            ->setMockClassName('SearchTables')
            ->setMethods(['filterParams', 'behaviors'])
            ->getMock();
        $tableMock->expects($this->once())
            ->method('filterParams')
            ->will($this->returnValue($params));
        $tableMock->expects($this->once())
            ->method('behaviors')
            ->will($this->returnValue($behaviorRegistryMock));

        \Cake\ORM\TableRegistry::set('Search', $tableMock);

        $queryMock = $this->getMockBuilder('\Cake\ORM\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('find')
            ->with('search', $params)
            ->will($this->returnValue($queryMock));

        $subject = new \Crud\Event\Subject();
        $subject->query = $queryMock;

        $event = new \Cake\Event\Event('Crud.beforeLookup', $subject);

        $listener = new \Crud\Listener\SearchListener($controller, [
            'enabled' => [
                'Crud.beforeLookup'
            ]
        ]);
        $listener->injectSearch($event);
    }
}
