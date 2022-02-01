<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Error;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Crud\Error\Exception\ValidationException;
use Crud\Error\ExceptionRenderer;
use Crud\Log\QueryLogger;

class ExceptionRendererTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Configure::write('debug', true);
    }

    public function testNormalExceptionRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $serialize = $Controller->viewBuilder()->getOption('serialize');

        $this->assertNotEmpty($serialize);
        $this->assertNotFalse(array_search('success', $serialize));
        $this->assertNotFalse(array_search('data', $serialize));

        $viewVars = $Controller->viewBuilder()->getVars();

        $expected = [
            'code' => 500,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\CakeException',
                'code' => 0,
                'message' => 'Hello World',
            ],
        ];

        if (version_compare(Configure::version(), '4.2.0', '<')) {
            $expected['exception']['class'] = 'Cake\Core\Exception\Exception';
            $expected['exception']['code'] = 500;
        }

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
        $this->assertEquals($expected, $actual);

        $this->assertTrue(!isset($actual['queryLog']));

        $this->assertTrue(isset($viewVars['success']));
        $this->assertFalse($viewVars['success']);

        $this->assertTrue(isset($viewVars['code']));
        $this->assertSame(500, $viewVars['code']);

        $this->assertTrue(isset($viewVars['url']));
        $this->assertSame($Controller->getRequest()->getRequestTarget(), $viewVars['url']);

        $this->assertTrue(isset($viewVars['message']));
        $this->assertSame('Hello World', $viewVars['message']);

        $this->assertTrue(isset($viewVars['error']));
        $this->assertSame($Exception, $viewVars['error']);
    }

    public function testNormalExceptionRenderingQueryLog()
    {
        $Exception = new Exception('Hello World');

        $QueryLogger = $this->getMockBuilder(QueryLogger::class)
            ->onlyMethods(['getLogs'])
            ->getMock();
        $currentLogger = ConnectionManager::get('test')->getLogger();
        ConnectionManager::get('test')->setLogger($QueryLogger);

        $QueryLogger
            ->expects($this->once())
            ->method('getLogs')
            ->with()
            ->will($this->returnValue(['query']));

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();
        $Controller->request = new ServerRequest();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $serialize = $Controller->viewBuilder()->getOption('serialize');

        $this->assertNotEmpty($serialize);
        $this->assertNotFalse(array_search('success', $serialize));
        $this->assertNotFalse(array_search('data', $serialize));

        $viewVars = $Controller->viewBuilder()->getVars();

        $expected = [
            'code' => 500,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\CakeException',
                'code' => 0,
                'message' => 'Hello World',
            ],
        ];

        if (version_compare(Configure::version(), '4.2.0', '<')) {
            $expected['exception']['class'] = 'Cake\Core\Exception\Exception';
            $expected['exception']['code'] = 500;
        }

        $actual = $viewVars['data'];
        $queryLog = $viewVars['queryLog'];

        unset($actual['trace'], $actual['file'], $actual['line']);
        $this->assertEquals($expected, $actual);

        $this->assertNotEmpty($queryLog);
        $this->assertTrue(isset($queryLog['test']));
        $this->assertEquals('query', $queryLog['test'][0]);

        $this->assertTrue(isset($viewVars['success']));
        $this->assertFalse($viewVars['success']);

        $this->assertTrue(isset($viewVars['code']));
        $this->assertSame(500, $viewVars['code']);

        $this->assertTrue(isset($viewVars['url']));
        $this->assertSame($Controller->getRequest()->getRequestTarget(), $viewVars['url']);

        $this->assertTrue(isset($viewVars['message']));
        $this->assertSame('Hello World', $viewVars['message']);

        $this->assertTrue(isset($viewVars['error']));
        $this->assertSame($Exception, $viewVars['error']);

        ConnectionManager::get('test')->setLogger($currentLogger);
    }

    public function testNormalNestedExceptionRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();
        $Controller->request = new ServerRequest();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $serialize = $Controller->viewBuilder()->getOption('serialize');

        $this->assertNotEmpty($serialize);
        $this->assertNotFalse(array_search('success', $serialize));
        $this->assertNotFalse(array_search('data', $serialize));

        $viewVars = $Controller->viewBuilder()->getVars();

        $expected = [
            'code' => 500,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\CakeException',
                'code' => 0,
                'message' => 'Hello World',
            ],
        ];

        if (version_compare(Configure::version(), '4.2.0', '<')) {
            $expected['exception']['class'] = 'Cake\Core\Exception\Exception';
            $expected['exception']['code'] = 500;
        }

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
        $this->assertEquals($expected, $actual);

        $this->assertTrue(isset($viewVars['success']));
        $this->assertFalse($viewVars['success']);

        $this->assertTrue(isset($viewVars['code']));
        $this->assertSame(500, $viewVars['code']);

        $this->assertTrue(isset($viewVars['url']));
        $this->assertSame($Controller->getRequest()->getRequestTarget(), $viewVars['url']);

        $this->assertTrue(isset($viewVars['message']));
        $this->assertSame('Hello World', $viewVars['message']);

        $this->assertTrue(isset($viewVars['error']));
        $this->assertSame($Exception, $viewVars['error']);
    }

    public function testMissingViewExceptionDuringRendering()
    {
        $Exception = new Exception('Hello World');

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $serialize = $Controller->viewBuilder()->getOption('serialize');

        $this->assertNotEmpty($serialize);
        $this->assertNotFalse(array_search('success', $serialize));
        $this->assertNotFalse(array_search('data', $serialize));

        $viewVars = $Controller->viewBuilder()->getVars();

        $expected = [
            'code' => 500,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\CakeException',
                'code' => 0,
                'message' => 'Hello World',
            ],
        ];

        if (version_compare(Configure::version(), '4.2.0', '<')) {
            $expected['exception']['class'] = 'Cake\Core\Exception\Exception';
            $expected['exception']['code'] = 500;
        }

        $actual = $viewVars['data'];
        unset($actual['trace'], $actual['file'], $actual['line']);
        $this->assertEquals($expected, $actual);

        $this->assertTrue(isset($viewVars['success']));
        $this->assertFalse($viewVars['success']);

        $this->assertTrue(isset($viewVars['code']));
        $this->assertSame(500, $viewVars['code']);

        $this->assertTrue(isset($viewVars['url']));
        $this->assertSame($Controller->getRequest()->getRequestTarget(), $viewVars['url']);

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

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $serialize = $Controller->viewBuilder()->getOption('serialize');

        $this->assertNotEmpty($serialize);
        $this->assertEquals(['success', 'data'], $serialize);

        $viewVars = $Controller->viewBuilder()->getVars();

        $expected = [
            'code' => 500,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => 'Hello World',
            'exception' => [
                'class' => 'Cake\Core\Exception\Exception',
                'code' => 500,
                'message' => 'Hello World',
            ],
        ];
        $actual = $viewVars['data'];
        unset($actual['trace']);
        $this->assertEquals($expected, $actual);

        $this->assertTrue(isset($viewVars['success']));
        $this->assertFalse($viewVars['success']);

        $this->assertTrue(isset($viewVars['code']));
        $this->assertSame(500, $viewVars['code']);

        $this->assertTrue(isset($viewVars['url']));
        $this->assertSame($Controller->getRequest()->getRequestTarget(), $viewVars['url']);

        $this->assertTrue(isset($viewVars['message']));
        $this->assertSame('Generic Exception Description', $viewVars['message']);

        $this->assertTrue(isset($viewVars['error']));
        $this->assertSame($NestedException, $viewVars['error']);
    }

    public function testValidationErrorSingleKnownError()
    {
        Configure::write('debug', false);

        $entity = new Entity();
        $entity->setErrors(['title' => ['error message']]);

        $Exception = new ValidationException($entity);

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();
        $Controller->request = new ServerRequest();
        $Controller->response = new Response();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
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
            'url' => $Controller->getRequest()->getRequestTarget(),
            'errorCount' => 1,
            'errors' => [
                'title' => [
                    'error message',
                ],
            ],
            'message' => 'A validation error occurred',
        ];
        $this->assertEquals($expected, $Controller->viewBuilder()->getVar('data'));
    }

    public function testValidationErrorSingleKnownErrorWithDebug()
    {
        Configure::write('debug', true);

        $entity = new Entity();
        $entity->setErrors(['title' => ['error message']]);

        $Exception = new ValidationException($entity);

        /** @var \Cake\Controller\Controller&\PHPUnit\Framework\MockObject\MockObject $Controller */
        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
            ->disableOriginalConstructor()
            ->getMock();
        $Renderer
            ->expects($this->once())
            ->method('_getController')
            ->with()
            ->will($this->returnValue($Controller));

        $Renderer->__construct($Exception);
        $Renderer->render();

        $result = $Controller->viewBuilder()->getVar('data');
        $this->assertContains('trace', array_keys($Controller->viewBuilder()->getVar('data')));
        unset($result['trace']);

        $expected = [
            'code' => 422,
            'url' => $Controller->getRequest()->getRequestTarget(),
            'errorCount' => 1,
            'errors' => [
                'title' => [
                    'error message',
                ],
            ],
            'exception' => [
                'class' => 'Crud\Error\Exception\ValidationException',
                'code' => 422,
                'message' => 'A validation error occurred',
            ],
            'message' => 'A validation error occurred',
        ];
        $this->assertEquals($expected, $result);
    }

    public function testValidationErrorMultipleMessages()
    {
        $entity = new Entity();
        $entity->setErrors([
            'title' => ['error message'],
            'body' => ['another field message'],
        ]);

        $Exception = new ValidationException($entity);

        $Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['render'])
            ->getMock();

        $Renderer = $this->getMockBuilder(ExceptionRenderer::class)
            ->onlyMethods(['_getController'])
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
            'url' => $Controller->getRequest()->getRequestTarget(),
            'message' => '2 validation errors occurred',
            'errorCount' => 2,
            'errors' => [
                'title' => [
                    'error message',
                ],
                'body' => [
                    'another field message',
                ],
            ],
            'exception' => [
                'class' => 'Crud\Error\Exception\ValidationException',
                'code' => 422,
                'message' => '2 validation errors occurred',
            ],
        ];
        $data = $Controller->viewBuilder()->getVar('data');
        unset($data['trace']);
        $this->assertEquals($expected, $data);
    }
}
