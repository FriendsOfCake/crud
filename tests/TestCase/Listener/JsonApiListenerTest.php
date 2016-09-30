<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Crud\Listener\JsonApiListener;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiListenerTest extends TestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = [
        'core.articles',
        'core.authors',
        'core.comments'
    ];

    /**
     * Make sure we are testing with expected default configuration values.
     */
    public function testDefaultConfig()
    {
        $listener = new JsonApiListener(new Controller());

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

        $this->assertSame($expected, $listener->config());
    }

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
     * Make sure listener continues if neomerx package is installed
     *
     * @return void
     */
    public function testCheckPackageDependenciesSuccess()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkPackageDependencies', [], $listener);
    }

    /**
     * Make sure listener stops if neomerx package is not installed
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener requires composer installing neomerx/json-api:^0.8.10
     */
    public function testCheckPackageDependenciesFail()
    {
        $this->markTestIncomplete(
            'Might be impossible to test due to inability to unload loaded classes)'
        );
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

    /**
     * Make sure the listener accepts the correct request headers

     */
    public function testCheckRequestMethodsSuccess()
    {
        $request = new Request();
        $request->env('HTTP_ACCEPT', 'application/vnd.api+json');
        $response = new Response();
        $controller = new Controller($request, $response);
        $listener = new JsonApiListener($controller);
        $listener->setupDetectors();

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkRequestMethods', [], $listener);

        $request = new Request();
        $request->env('HTTP_ACCEPT', 'application/vnd.api+json');
        $request->env('CONTENT_TYPE', 'application/vnd.api+json');
        $response = new Response();
        $controller = new Controller($request, $response);
        $listener = new JsonApiListener($controller);
        $listener->setupDetectors();

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkRequestMethods', [], $listener);
    }

    /**
     * Make sure the listener fails on non JSON API request Accept Type header
     *
     * @expectedException \Cake\Network\Exception\BadRequestException
     * @expectedExceptionMessage JsonApiListener requests require the application/vnd.api+json Accept header
     */
    public function testCheckRequestMethodsFailAcceptHeader()
    {
        $request = new Request();
        $request->env('HTTP_ACCEPT', 'application/json');
        $response = new Response();
        $controller = new Controller($request, $response);
        $listener = new JsonApiListener($controller);
        $listener->setupDetectors();

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkRequestMethods', [], $listener);
    }

    /**
     * Make sure the listener fails on non JSON API request Content-Type header
     *
     * @expectedException \Cake\Network\Exception\BadRequestException
     * @expectedExceptionMessage Posting data to JsonApiListener requires the application/vnd.api+json Content-Type header
     */
    public function testCheckRequestMethodsFailContentHeader()
    {
        $request = new Request();
        $request->env('HTTP_ACCEPT', 'application/vnd.api+json');
        $request->env('CONTENT_TYPE', 'application/json');
        $response = new Response();
        $controller = new Controller($request, $response);
        $listener = new JsonApiListener($controller);
        $listener->setupDetectors();

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_checkRequestMethods', [], $listener);
    }

    /**
     * Make sure correct find data is returned from subject based on action
     *
     */
    public function testGetFindResultForIndex()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(null)
            ->enableOriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller'])
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->getMock();
        $subject->entities = 'index-should-return-entities-property';
        $subject->entity = 'all-other-actions-should-return-entity-property';

        $controller->request->action = 'index';
        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getFindResult', [$subject], $listener);
        $this->assertSame($subject->entities, $result);

        $controller->request->action = 'any-other-action-name';
        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getFindResult', [$subject], $listener);
        $this->assertSame($subject->entity, $result);
    }

    /**
     * Make sure associations not present in the find result are stripped
     * from the AssociationCollection. In this test we remove associated
     * model `Comments`.
     */
    public function testStripAssociations()
    {
        $table = TableRegistry::get('Articles');
        $table->belongsTo('Authors');
        $table->hasMany('Comments');

        // make sure expected associations are there
        $associationsBefore = $table->associations();
        $this->assertNotEmpty($associationsBefore->get('authors'));
        $this->assertNotEmpty($associationsBefore->get('comments'));

        // make sure comments are not present in the find result
        $query = $table->find()->contain([
            'Authors'
        ]);
        $entity = $query->first();

        $this->assertNotEmpty($entity->author);
        $this->assertNull($entity->comments);

        // make sure comments are removed from AssociationCollection
        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $associationsAfter = $this->callProtectedMethod('_stripAssociations', [$table, $entity], $listener);

        $this->assertNotEmpty($associationsAfter->get('authors'));
        $this->assertNull($associationsAfter->get('comments'));
    }

    /**
     * Make sure we get a list of entity names for the current entity (name
     * passed as string) and all associated models.
     */
    public function testGetEntityList()
    {
        $table = TableRegistry::get('Articles');
        $table->belongsTo('Authors');
        $table->hasMany('Comments');

        $associations = $table->associations();

        $this->assertNotEmpty($associations->get('authors'));
        $this->assertNotEmpty($associations->get('comments'));

        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getEntityList', ['Article', $associations], $listener);

        $expected = [
            'Article',
            'Author',
            'Comment'
        ];

        $this->assertSame($expected, $result);
    }
}
