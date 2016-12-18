<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Crud\Listener\ApiQueryLogListener;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLogListenerTest extends TestCase
{

    protected $_debug;

    public function setUp()
    {
        parent::setUp();
        $this->_debug = Configure::read('debug');
    }

    public function tearDown()
    {
        parent::tearDown();
        Configure::write('debug', $this->_debug);
    }

    /**
     * Test implemented events
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiQueryLogListener')
            ->disableOriginalConstructor()
            ->setMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(true));

        $result = $Instance->implementedEvents();
        $expected = [
            'Crud.beforeFilter' => ['callable' => [$Instance, 'setupLogging'], 'priority' => 1],
            'Crud.beforeRender' => ['callable' => [$Instance, 'beforeRender'], 'priority' => 75]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test implemented events without API request
     *
     * @return void
     */
    public function testImplementedEventsNotApiRequest()
    {
        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiQueryLogListener')
            ->disableOriginalConstructor()
            ->setMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(false));

        $result = $Instance->implementedEvents();
        $expected = [];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that calling beforeRender with debug to false
     * will not ask for request type
     *
     * @return void
     */
    public function testBeforeRenderDebugFalse()
    {
        Configure::write('debug', false);

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiQueryLogListener')
            ->disableOriginalConstructor()
            ->setMethods(['_getQueryLogs'])
            ->getMock();
        $Instance
            ->expects($this->never())
            ->method('_getQueryLogs');

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test that calling beforeRender with debug to true
     * will ask for request type but won't ask for serialize configuration
     * since it's not an API request
     *
     * @return void
     */
    public function testBeforeRenderDebugTrue()
    {
        Configure::write('debug', true);

        $Action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->disableOriginalConstructor()
            ->setMethods(['config'])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('config')
            ->with('serialize.queryLog', 'queryLog');

        $Controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('queryLog', []);

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiQueryLogListener')
            ->disableOriginalConstructor()
            ->setMethods(['_getQueryLogs', '_action', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($Action));
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($Controller));
        $Instance
            ->expects($this->once())
            ->method('_getQueryLogs')
            ->will($this->returnValue([]));

        $Instance->beforeRender(new Event('something'));
    }

    /**
     * Test setting up the query loggers
     *
     * @return void
     */
    public function testSetupLogging()
    {
        $DefaultSource = $this
            ->getMockBuilder('\Cake\Database\Connection')
            ->disableOriginalConstructor()
            ->setMethods(['logQueries', 'logger'])
            ->getMock();
        $DefaultSource
            ->expects($this->once())
            ->method('logQueries')
            ->with(true);
        $DefaultSource
            ->expects($this->once())
            ->method('logger')
            ->with($this->isInstanceOf('\Crud\Log\QueryLogger'));

        $Instance = $this
            ->getMockBuilder('\Crud\Listener\ApiQueryLogListener')
            ->disableOriginalConstructor()
            ->setMethods(['_getSources', '_getSource'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_getSources')
            ->will($this->returnValue(['default']));
        $Instance
            ->expects($this->any())
            ->method('_getSource')
            ->with('default')
            ->will($this->returnValue($DefaultSource));

        $Instance->setupLogging(new Event('something'));
    }

    /**
     * Test getting query logs using protected method
     *
     * @return void
     */
    public function testProtectedGetQueryLogs()
    {
        $listener = new ApiQueryLogListener(new Controller());
        $listener->setupLogging(new Event('something'));
        $this->setReflectionClassInstance($listener);

        $expected = [
            'test' => []
        ];

        $this->assertEquals($expected, $this->callProtectedMethod('_getQueryLogs', [], $listener));
    }

    /**
     * Test getting query logs using public getter.
     *
     * @return void
     */
    public function testPublicGetQueryLogs()
    {
        $listener = new ApiQueryLogListener(new Controller());
        $listener->setupLogging(new Event('something'));

        $expected = [
            'test' => []
        ];

        $this->assertEquals($expected, $listener->getQueryLogs());
    }
}
