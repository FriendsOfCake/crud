<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Crud\Listener\JsonApiListener;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiListenerTest extends TestCase
{
    /**
     * Test implementedEvents with API request
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['foobar'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->RequestHandler = $this->getMockBuilder('\Cake\Controller\Component\RequestHandlerComponent')
            ->setMethods(['config'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->setMethods(['setupDetectors', '_controller'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $result = $listener->implementedEvents();

        $expected = [
            'Crud.beforeFilter' => ['callable' => [$listener, 'setupLogging'], 'priority' => 1],
            'Crud.beforeHandle' => ['callable' => [$listener, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$listener, 'setFlash'], 'priority' => 5],
            'Crud.beforeRender' => ['callable' => [$listener, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$listener, 'respond'], 'priority' => 100]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test default configuration
     *
     * @return void
     */
    public function testDefaultConfiguration()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expected = [
            'detectors' => [
                'jsonapi' => ['ext' => 'json', 'accepts' => 'application/vnd.api+json'],
            ],
            'exception' => [
                'type' => 'default',
                'class' => 'Cake\Network\Exception\BadRequestException',
                'message' => 'Unknown error',
                'code' => 0,
            ],
            'exceptionRenderer' => 'Crud\Error\JsonApiExceptionRenderer',
            'setFlash' => false,
            'urlPrefix' => null,
            'withJsonApiVersion' => false,
            'meta' => false,
            'include' => [],
            'fieldSets' => [],
        ];
        $result = $listener->config();
        $this->assertEquals($expected, $result);
    }

    /**
     * Make sure config option `urlPrefix` does not accept an array
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `urlPrefix` only accepts a string
     */
    public function testValidateConfigOptionUrlPrefixFailWithArray()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'urlPrefix' => ['array', 'not-accepted']
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `withJsonApiVersion` accepts a boolean
     *
     * @return void
     */
    public function testValidateConfigOptionWithJsonApiVersionSuccessWithBoolean()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'withJsonApiVersion' => true
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `withJsonApiVersion` accepts an array
     *
     * @return void
     */
    public function testValidateConfigOptionWithJsonApiVersionSuccessWithArray()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'withJsonApiVersion' => ['array' => 'accepted']
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `withJsonApiVersion` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `withJsonApiVersion` only accepts a boolean or an array
     */
    public function testValidateConfigOptionWithJsonApiVersionFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'withJsonApiVersion' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `meta` accepts an array
     *
     * @return void
     */
    public function testValidateConfigOptionMetaSuccessWithArray()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'meta' => ['array' => 'accepted']
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `meta` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `meta` only accepts an array
     */
    public function testValidateConfigOptionMetaFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'meta' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }


    /**
     * Make sure config option `include` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `include` only accepts an array
     */
    public function testValidateConfigOptionIncludeFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'include' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `fieldSets` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `fieldSets` only accepts an array
     */
    public function testValidateConfigOptionFieldSetsFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'fieldSets' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }
}
