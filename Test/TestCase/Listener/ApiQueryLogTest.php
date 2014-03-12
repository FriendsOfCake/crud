<?php
namespace Crud\Test\TestCase\Listener;

use Crud\TestSuite\TestCase;
use Cake\Core\Configure;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiQueryLogTest extends TestCase {

	protected $_debug;

	public function setUp() {
		parent::setUp();
		$this->_debug = Configure::read('debug');
	}

	public function tearDown() {
		parent::tearDown();
		Configure::write('debug', $this->_debug);
	}

/**
 * Test implemented events
 *
 * @covers Crud\Listener\ApiQueryLog::implementedEvents
 * @return void
 */
	public function testImplementedEvents() {
		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
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
			'Crud.initialize' => ['callable' => [$Instance, 'setupLogging'], 'priority' => 1],
			'Crud.beforeRender' => ['callable' => [$Instance, 'beforeRender'], 'priority' => 75]
		];

		$this->assertEquals($expected, $result);
	}

/**
 * Test implemented events without API request
 *
 * @covers Crud\Listener\ApiQueryLog::implementedEvents
 * @return void
 */
	public function testImplementedEventsNotApiRequest() {
		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
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
 * Test that calling beforeRender with debug 0
 * will not ask for request type
 *
 * @covers ApiQueryLogListener::beforeRender
 * @return void
 */
	public function testBeforeRenderDebugZero() {
		Configure::write('debug', 0);

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
			->disableOriginalConstructor()
			->setMethods(['_getQueryLogs'])
			->getMock();
		$Instance
			->expects($this->never())
			->method('_getQueryLogs');

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test that calling beforeRender with debug 1
 * will not ask for request type
 *
 * @covers ApiQueryLogListener::beforeRender
 * @return void
 */
	public function testBeforeRenderDebugOne() {
		Configure::write('debug', 1);

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
			->disableOriginalConstructor()
			->setMethods(['_getQueryLogs'])
			->getMock();
		$Instance
			->expects($this->never())
			->method('_getQueryLogs');

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test that calling beforeRender with debug 2
 * will ask for request type but won't ask for serialize configuration
 * since it's not an API request
 *
 * @covers ApiQueryLogListener::beforeRender
 * @return void
 */
	public function testBeforeRenderDebugTwo() {
		Configure::write('debug', 2);

		$Action = $this
			->getMockBuilder('\Crud\Action\Base')
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
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
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

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test setting up the query loggers
 *
 * @covers ApiQueryLogListener::setupLogging
 * @return void
 */
	public function testSetupLogging() {
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
			->getMockBuilder('\Crud\Listener\ApiQueryLog')
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

		$Instance->setupLogging(new \Cake\Event\Event('something'));
	}

}
