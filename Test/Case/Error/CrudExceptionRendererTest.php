<?php

App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('CrudExceptionRenderer', 'Crud.Error');

class CrudExceptionRendererTest extends CakeTestCase {

	public function testNormalExceptionRendering() {
		Configure::write('debug', 1);
		$Exception = new CakeException('Hello World');

		$Controller = $this->getMock('Controller', array('render'));
		$Controller->request = new CakeRequest();
		$Controller->response = new CakeResponse();

		$Renderer = $this->getMock('CrudExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with($Exception)
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEqual($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'name' => 'Hello World',
			'exception' => array(
				'class' => 'CakeException',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEqual($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['name']));
		$this->assertSame('Hello World', $viewVars['name']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);
	}

	public function testMissingViewExceptionDuringRendering() {
		Configure::write('debug', 1);
		$Exception = new CakeException('Hello World');

		$Controller = $this->getMock('Controller', array('render'));
		$Controller->request = new CakeRequest();
		$Controller->response = $this->getMock('CakeResponse', array('send'));
		$Controller->response
			->expects($this->at(0))
			->method('send')
			->will($this->throwException(new MissingViewException('boo')));

		$Renderer = $this->getMock('CrudExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with($Exception)
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEqual($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'name' => 'Hello World',
			'exception' => array(
				'class' => 'CakeException',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEqual($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['name']));
		$this->assertSame('Hello World', $viewVars['name']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($Exception, $viewVars['error']);
	}

	public function testGenericExceptionDuringRendering() {
		Configure::write('debug', 1);

		$Exception = new CakeException('Hello World');
		$NestedException = new CakeException('Generic Exception Description');

		$Controller = $this->getMock('Controller', array('render'));
		$Controller->request = new CakeRequest();
		$Controller->response = $this->getMock('CakeResponse', array('send'));
		$Controller->response
			->expects($this->at(0))
			->method('send')
			->will($this->throwException($NestedException));

		$Renderer = $this->getMock('CrudExceptionRenderer', array('_getController'), array(), '', false);
		$Renderer
			->expects($this->once())
			->method('_getController')
			->with($Exception)
			->will($this->returnValue($Controller));

		$Renderer->__construct($Exception);
		$Renderer->render();

		$viewVars = $Controller->viewVars;

		$this->assertTrue(!empty($viewVars['_serialize']));

		$expected = array('success', 'data');
		$actual = $viewVars['_serialize'];
		$this->assertEqual($expected, $actual);

		$expected = array(
			'code' => 500,
			'url' => $Controller->request->here(),
			'name' => 'Hello World',
			'exception' => array(
				'class' => 'CakeException',
				'code' => 500,
				'message' => 'Hello World',
			)
		);
		$actual = $viewVars['data'];
		unset($actual['exception']['trace']);
		$this->assertEqual($expected, $actual);

		$this->assertTrue(isset($viewVars['success']));
		$this->assertFalse($viewVars['success']);

		$this->assertTrue(isset($viewVars['code']));
		$this->assertSame(500, $viewVars['code']);

		$this->assertTrue(isset($viewVars['url']));
		$this->assertSame($Controller->request->here(), $viewVars['url']);

		$this->assertTrue(isset($viewVars['name']));
		$this->assertSame('Generic Exception Description', $viewVars['name']);

		$this->assertTrue(isset($viewVars['error']));
		$this->assertSame($NestedException, $viewVars['error']);
	}


}
