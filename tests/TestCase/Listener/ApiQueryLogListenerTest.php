<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Crud\Action\BaseAction;
use Crud\Listener\ApiQueryLogListener;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLogListenerTest extends TestCase
{
    protected $_debug;

    protected function setUp(): void
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
            ->willReturn(true);

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
            ->willReturn(false);

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
            ->onlyMethods(['getQueryLogs'])
            ->getMock();
        $Instance
            ->expects($this->never())
            ->method('getQueryLogs');

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
            ->onlyMethods(['getQueryLogs', '_action', '_controller'])
            ->getMock();
        $Instance
            ->expects($this->once())
            ->method('_action')
            ->willReturn($Action);
        $Instance
            ->expects($this->once())
            ->method('_controller')
            ->willReturn($Controller);
        $Instance
            ->expects($this->once())
            ->method('getQueryLogs')
            ->willReturn([]);

        $Instance->beforeRender(new Event('something'));
    }

    public function testQueryLogs()
    {
        $listener = new ApiQueryLogListener(new Controller(new ServerRequest()));
        $listener->setupLogging(new Event('something'));

        $expected = [
            'test' => [],
        ];

        $this->assertEquals($expected, $listener->getQueryLogs());
    }
}
