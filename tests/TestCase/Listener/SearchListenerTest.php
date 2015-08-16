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

        $subject = new \Crud\Event\Subject();

        $behavior = $this
            ->getMockBuilder('\Search\Model\Behavior\SearchBehavior')
            ->setMethods(['filterParams'])
            ->disableoriginalConstructor()
            ->getMock();

        $behavior
            ->expects($this->once())
            ->method('filterParams')
            ->will($this->returnValue([
                'search' => [
                    'name' => '1st post'
                ]
            ]));

        $blogs = \Cake\ORM\TableRegistry::get('Blogs');
        $subject->query = $blogs->find();

        

    }
}
