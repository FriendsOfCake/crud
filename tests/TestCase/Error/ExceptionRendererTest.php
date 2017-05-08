<?php
namespace Crud\Test\TestCase\Error;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Crud\Error\Exception\ValidationException;

class ExceptionRendererTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Configure::write('debug', true);
    }

    public function testNormalExceptionRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $viewVars = $Controller->viewVars;

        $this->assertTrue(!empty($viewVars['_serialize']));

        $expected = ['success', 'data'];
        $actual = $viewVars['_serialize'];
        $this->assertEquals($expected, $actual);

        $expected = [
            'code' => 500,
            'url' => $Controller->request->here(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ]
        ];

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
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

    public function testNormalExceptionRenderingQueryLog()
    {
        $Exception = new Exception('Hello World');

        $QueryLogger = $this->getMockBuilder('Crud\Log\QueryLogger')
            ->setMethods(['getLogs'])
            ->getMock();
        $currentLogger = ConnectionManager::get('test')->logger();
        ConnectionManager::get('test')->logger($QueryLogger);

        $QueryLogger
            ->expects($this->once())
            ->method('getLogs')
            ->with()
            ->will($this->returnValue(['query']));

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $viewVars = $Controller->viewVars;

        $this->assertTrue(!empty($viewVars['_serialize']));

        $expected = ['success', 'data', 'queryLog'];
        $actual = $viewVars['_serialize'];
        $this->assertEquals($expected, $actual);

        $expected = [
            'code' => 500,
            'url' => $Controller->request->here(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ]
        ];

        $actual = $viewVars['data'];
        $queryLog = $viewVars['queryLog'];

        unset($actual['trace'], $actual['file'], $actual['line']);
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

    public function testNormalNestedExceptionRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $viewVars = $Controller->viewVars;

        $this->assertTrue(!empty($viewVars['_serialize']));

        $expected = ['success', 'data'];
        $actual = $viewVars['_serialize'];
        $this->assertEquals($expected, $actual);

        $expected = [
            'code' => 500,
            'url' => $Controller->request->here(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ]
        ];

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
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

    public function testMissingViewExceptionDuringRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = $this->getMockBuilder('Cake\Network\Response')
            ->setMethods(['send'])
            ->getMock();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $viewVars = $Controller->viewVars;

        $this->assertTrue(!empty($viewVars['_serialize']));

        $expected = ['success', 'data'];
        $actual = $viewVars['_serialize'];
        $this->assertEquals($expected, $actual);

        $expected = [
            'code' => 500,
            'url' => $Controller->request->here(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ]
        ];

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
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

    public function testGenericExceptionDuringRendering()
    {
        $this->markTestSkipped();

        $Exception = new Exception('Hello World');
        $NestedException = new Exception('Generic Exception Description');

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = $this->getMockBuilder('Cake\Network\Response')
            ->getMock();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $viewVars = $Controller->viewVars;

        $this->assertTrue(!empty($viewVars['_serialize']));

        $expected = ['success', 'data'];
        $actual = $viewVars['_serialize'];
        $this->assertEquals($expected, $actual);

        $expected = [
            'code' => 500,
            'url' => $Controller->request->here(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ]
        ];
        $actual = $viewVars['data'];
        unset($actual['trace']);
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

    public function testValidationErrorSingleKnownError()
    {
        Configure::write('debug', false);

        $entity = new Entity();
        $entity->errors('title', ['error message']);

        $Exception = new ValidationException($entity);

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $expected = [
            'code' => 422,
            'url' => $Controller->request->here(),
            'errorCount' => 1,
            'errors' => [
                'title' => [
                    'error message'
                ]
            ],
            'message' => 'A validation error occurred'
        ];
        $this->assertEquals($expected, $Controller->viewVars['data']);
    }

    public function testValidationErrorSingleKnownErrorWithDebug()
    {
        Configure::write('debug', true);

        $entity = new Entity();
        $entity->errors('title', ['error message']);

        $Exception = new ValidationException($entity);

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $this->assertContains('trace', array_keys($Controller->viewVars['data']));
        unset($Controller->viewVars['data']['trace']);

        $expected = [
            'code' => 422,
            'url' => $Controller->request->here(),
            'errorCount' => 1,
            'errors' => [
                'title' => [
                    'error message'
                ]
            ],
            'exception' => [
                'class' => 'Crud\Error\Exception\ValidationException',
                'code' => 422,
                'message' => 'A validation error occurred'
            ],
            'message' => 'A validation error occurred'
        ];
        $this->assertEquals($expected, $Controller->viewVars['data']);
    }

    public function testValidationErrorMultipleMessages()
    {
        $entity = new Entity();
        $entity->errors([
            'title' => ['error message'],
            'body' => ['another field message']
        ]);

        $Exception = new ValidationException($entity);

        $Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['render'])
            ->getMock();
        $Controller->request = new Request();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder('Crud\Error\ExceptionRenderer')
            ->setMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $expected = [
            'code' => 422,
            'url' => $Controller->request->here(),
            'message' => '2 validation errors occurred',
            'errorCount' => 2,
            'errors' => [
                'title' => [
                    'error message'
                ],
                'body' => [
                    'another field message'
                ]
            ],
            'exception' => [
                'class' => 'Crud\Error\Exception\ValidationException',
                'code' => 422,
                'message' => '2 validation errors occurred',
            ]
        ];
        $data = $Controller->viewVars['data'];
        unset($data['trace']);
        $this->assertEquals($expected, $data);
    }
}
