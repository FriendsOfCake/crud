<?php
namespace Crud\TestCase\Traits;

use Cake\Event\Event;
use Crud\TestSuite\TestCase;
use Crud\Traits\QueryLogTrait;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class QueryLogTraitTest extends TestCase
{
    use QueryLogTrait;

    /**
     * tearDown method used to make sure logging is disabled again to
     * not disrupt other tests expecting disabled query logging
     *
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->_getSources() as $connectionName) {
            try {
                $this->_getSource($connectionName)->logQueries(false);
            } catch (\Cake\Datasource\Exception\MissingDatasourceConfigException $e) {
                //Safe to ignore this :-)
            }
        }
    }

    /**
     * Test setting up the query loggers
     *
     * @return void
     */
    public function testSetupLogging()
    {
        $defaultSource = $this
            ->getMockBuilder('\Cake\Database\Connection')
            ->disableOriginalConstructor()
            ->setMethods(['logQueries', 'logger'])
            ->getMock();
        $defaultSource
            ->expects($this->once())
            ->method('logQueries')
            ->with(true);
        $defaultSource
            ->expects($this->once())
            ->method('logger')
            ->with($this->isInstanceOf('\Crud\Log\QueryLogger'));

        $instance = $this
            ->getMockBuilder('\Crud\Traits\QueryLogTrait')
            ->disableOriginalConstructor()
            ->setMethods(['_getSources', '_getSource'])
            ->getMockForTrait();
        $instance
            ->expects($this->once())
            ->method('_getSources')
            ->will($this->returnValue(['default']));
        $instance
            ->expects($this->any())
            ->method('_getSource')
            ->with('default')
            ->will($this->returnValue($defaultSource));

        $instance->setupLogging(new Event('something'));
    }

    /**
     * Test getting query logs
     *
     * @return void
     */
    public function testGetQueryLogs()
    {
        $this->setupLogging(new Event('something'));
        $result = $this->_getQueryLogs();

        $expected = [
            'test' => []
        ];

        $this->assertEquals($expected, $result);
    }
}
