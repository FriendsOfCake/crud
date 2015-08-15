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
            'Crud.beforePaginate' => ['callable' => 'beforePaginate']
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test beforePaginate
     *
     * @return void
     */
    public function testBeforePaginate()
    {

    }
}
