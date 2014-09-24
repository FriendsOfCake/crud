<?php
namespace Crud\Test\TestCase\Error;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Cake\View\Exception\MissingViewException;
use Crud\Error\ExceptionRenderer;
use Crud\Error\Exception\ValidationException;

class ExceptionRendererTest extends TestCase {

	public function setUp() {
		parent::setUp();
		Configure::write('debug', true);
	}

	public function testNormalExceptionRendering() {
		$Exception = new Exception('Hello World');

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = new Response();

		$Renderer = $this->getMock('Crud\Error\ExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEquals($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'message' => 'Hello World',
			'exception' => array(
				'class' => 'Cake\Core\Exception\Exception',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEquals($expected, $actual);

		$this->assertTrue(!isset($actual['queryLog']));

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['message']));
		$this->assertSame('Hello World', $viewVars['message']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);
	}

	public function testNormalExceptionRenderingQueryLog() {
		$Exception = new Exception('Hello World');

		$QueryLogger = $this->getMock('Crud\Log\QueryLogger', ['getLogs']);
		$currentLogger = ConnectionManager::get('test')->logger();
		ConnectionManager::get('test')->logger($QueryLogger);

		$QueryLogger
			->expects($this->once())
			->method('getLogs')
			->with()
			->will($this->returnValue(['query']));

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = new Response();

		$Renderer = $this->getMock(
			'Crud\Error\ExceptionRenderer',
			array('_getController'),
			array(),
			'',
			false
		);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEquals($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'message' => 'Hello World',
			'exception' => array(
				'class' => 'Cake\Core\Exception\Exception',
				'code' => 500,
				'message' => 'Hello World',
			)
		);

		$actual = $viewVars['data'];
		$queryLog = $actual['queryLog'];

		unset($actual['exception']['trace']);
		unset($actual['queryLog']);
		$this->assertEquals($expected, $actual);

		$this->assertTrue(!empty($queryLog));
		$this->assertTrue(isset($queryLog['test']));
		$this->assertEquals('query', $queryLog['test'][0]);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['message']));
		$this->assertSame('Hello World', $viewVars['message']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);

		ConnectionManager::get('test')->logger($currentLogger);
	}

	public function testNormalNestedExceptionRendering() {
		$Exception = new Exception('Hello World');

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = new Response();

		$Renderer = $this->getMock('Crud\Error\ExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEquals($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'message' => 'Hello World',
			'exception' => array(
				'class' => 'Cake\Core\Exception\Exception',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEquals($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['message']));
		$this->assertSame('Hello World', $viewVars['message']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);
	}

	public function testMissingViewExceptionDuringRendering() {
		$Exception = new Exception('Hello World');

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = $this->getMock('Cake\Network\Response', array('send'));

		$Renderer = $this->getMock('Crud\Error\ExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEquals($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'message' => 'Hello World',
			'exception' => array(
				'class' => 'Cake\Core\Exception\Exception',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEquals($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['message']));
		$this->assertSame('Hello World', $viewVars['message']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);
	}

	public function testGenericExceptionDuringRendering() {
		$this->markTestSkipped();

		$Exception = new Exception('Hello World');
		$NestedException = new Exception('Generic Exception Description');

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = $this->getMock('Cake\Network\Response');

		$Renderer = $this->getMock(
			'Crud\Error\ExceptionRenderer',
			array('_getController'),
			array(),
			'',
			false
		);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEquals($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'message' => 'Hello World',
			'exception' => array(
				'class' => 'Cake\Core\Exception\Exception',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEquals($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['message']));
		$this->assertSame('Generic Exception Description', $viewVars['message']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($NestedException, $viewVars['error']);
	}

	public function testValidationErrorSingleKnownError() {
		Configure::write('debug', false);

		$entity = new Entity();
		$entity->errors('title', ['error message']);

		$Exception = new ValidationException($entity);

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = new Response();

		$Renderer = $this->getMock(
			'Crud\Error\ExceptionRenderer',
			array('_getController'),
			array(),
			'',
			false
		);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$expected = array(
			'code' => 412,
			'url' => $Controller->request->here(),
			'errorCount' => 1,
			'errors' => array(
				'title' => array(
					'error message'
				)
			),
			'exception' => array(
				'class' => 'Crud\Error\Exception\ValidationException',
				'code' => 412,
				'message' => 'A validation error occurred'
			),
			'message' => 'A validation error occurred'
		);
		$this->assertEquals($expected, $Controller->viewVars['data']);
	}

	public function testValidationErrorMultipleMessages() {
		$entity = new Entity();
		$entity->errors([
			'title' => ['error message'],
			'body' => ['another field message']
		]);

		$Exception = new ValidationException($entity);

		$Controller = $this->getMock('Cake\Controller\Controller', array('render'));
		$Controller->request = new Request();
		$Controller->response = new Response();

		$Renderer = $this->getMock(
			'Crud\Error\ExceptionRenderer',
			array('_getController'),
			array(),
			'',
			false
		);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with()
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$expected = array(
			'code' => 412,
			'url' => $Controller->request->here(),
			'message' => '2 validation errors occurred',
			'errorCount' => 2,
			'errors' => array(
				'title' => array(
					'error message'
				),
				'body' => array(
					'another field message'
				)
			),
			'exception' => array(
				'class' => 'Crud\Error\Exception\ValidationException',
				'code' => 412,
				'message' => '2 validation errors occurred',
			)
		);
		$data = $Controller->viewVars['data'];
		unset($data['exception']['trace']);
		$this->assertEquals($expected, $data);
	}

}
