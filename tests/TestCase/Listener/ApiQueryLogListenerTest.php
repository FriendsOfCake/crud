<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Event\Event;
use Crud\Action\BaseAction;
use Crud\Listener\ApiQueryLogListener;
use Crud\Log\QueryLogger;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLogListenerTest extends TestCase
{
    protected $_debug;

    public function setUp(): void
    {
        parent::setUp();
        $this->_debug = Configure::read('debug');
    }

    public function tearDown(): void
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
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_checkRequestType'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_checkRequestType')
            ->with('api')
            ->will($this->returnValue(true));

        $result = $Instance->implementedEvents();
        $expected = [
            'Crud.beforeFilter' => ['callable' => [$Instance, 'setupLogging'], 'priority' => 1],
            'Crud.beforeRender' => ['callable' => [$Instance, 'beforeRender'], 'priority' => 75],
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
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_checkRequestType'])
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
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_getQueryLogs'])
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
            ->getMockBuilder(BaseAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setConfig'])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('setConfig')
            ->with('serialize.queryLog', 'queryLog');

        $Controller = $this
            ->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['set'])
            ->getMock();
        $Controller
            ->expects($this->once())
            ->method('set')
            ->with('queryLog', []);

        $Instance = $this
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_getQueryLogs', '_action', '_controller'])
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
        $methodName = 'enableQueryLogging';
        if (version_compare(Configure::version(), '3.7.0RC', '<')) {
            $methodName = 'logQueries';
        }

        $DefaultSource = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([$methodName, 'setLogger'])
            ->getMock();
        $DefaultSource
            ->expects($this->once())
            ->method($methodName)
            ->with(true);
        $DefaultSource
            ->expects($this->once())
            ->method('setLogger')
            ->with($this->isInstanceOf(QueryLogger::class));

        $Instance = $this
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_getSources', '_getSource'])
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
     * Test setting up only specific query loggers
     *
     * @return void
     */
    public function testSetupLoggingConfiguredSources()
    {
        $methodName = 'enableQueryLogging';
        if (version_compare(Configure::version(), '3.7.0RC', '<')) {
            $methodName = 'logQueries';
        }

        $DefaultSource = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([$methodName, 'setLogger'])
            ->getMock();
        $DefaultSource
            ->expects($this->once())
            ->method($methodName)
            ->with(true);
        $DefaultSource
            ->expects($this->once())
            ->method('setLogger')
            ->with($this->isInstanceOf(QueryLogger::class));

        $TestSource = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([$methodName, 'setLogger'])
            ->getMock();
        $TestSource
            ->expects($this->once())
            ->method($methodName)
            ->with(true);
        $TestSource
            ->expects($this->once())
            ->method('setLogger')
            ->with($this->isInstanceOf(QueryLogger::class));

        $Instance = $this
            ->getMockBuilder(ApiQueryLogListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_getSources', '_getSource'])
            ->getMock();
        $Instance
            ->expects($this->never())
            ->method('_getSources');

        $Instance
            ->expects($this->at(0))
            ->method('_getSource')
            ->with('default')
            ->will($this->returnValue($DefaultSource));

        $Instance
            ->expects($this->at(1))
            ->method('_getSource')
            ->with('test')
            ->will($this->returnValue($TestSource));

        $Instance->setConfig('connections', ['default', 'test']);
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
            'test' => [],
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
            'test' => [],
        ];

        $this->assertEquals($expected, $listener->getQueryLogs());
    }
}
