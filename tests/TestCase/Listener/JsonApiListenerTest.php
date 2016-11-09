<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Controller\Controller;
use Cake\Core\Plugin;
use Cake\Filesystem\File;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Crud\Event\Subject;
use Crud\Listener\JsonApiListener;
use Crud\TestSuite\TestCase;
use Crud\Test\App\Model\Entity\Country;

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
        'plugin.crud.countries',
        'plugin.crud.cultures',
        'plugin.crud.currencies',
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
            'withJsonApiVersion' => false,
            'meta' => false,
            'urlPrefix' => null,
            'jsonOptions' => [],
            'debugPrettyPrint' => true,
            'include' => [],
            'fieldSets' => [],
            'docValidatorAboutLinks' => false,
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
            'Crud.beforeHandle' => ['callable' => [$listener, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$listener, 'setFlash'], 'priority' => 5],
            'Crud.afterSave' => ['callable' => [$listener, 'afterSave'], 'priority' => 90],
            'Crud.beforeRender' => ['callable' => [$listener, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$listener, 'respond'], 'priority' => 100]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test beforeHandle() method
     *
     * @return void
     */
    public function testBeforeHandle()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['_request'])
            ->disableOriginalConstructor()
            ->getMock();

        $controller->request = $this
            ->getMockBuilder('\Cake\Network\Request')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $controller->request->data = [
            'data' => [
                'type' => 'dummy',
                'attributes' => [],
            ]
        ];

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->setMethods(['_controller', '_checkRequestMethods', '_convertJsonApiDataArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $listener
            ->expects($this->any())
            ->method('_convertJsonApiDataArray')
            ->will($this->returnValue(true));

        $listener
            ->expects($this->any())
            ->method('_checkRequestMethods')
            ->will($this->returnValue(true));

        $listener->beforeHandle(new \Cake\Event\Event('Crud.beforeHandle'));
    }

    /**
     * Test afterSave event.
     */
    public function testAfterSave()
    {
        $this->markTestIncomplete(
            'Re-enable test once afterSave solution is ok (extra find to get belongTo relationships)'
        );

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller', '_response'])
            ->getMock();

        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $response = $this
            ->getMockBuilder('\Cake\Network\Response')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_response')
            ->will($this->returnValue($response));

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $event = $this
            ->getMockBuilder('\Cake\Event\Event')
            ->disableOriginalConstructor()
            ->setMethods(['subject'])
            ->getMock();

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $event
            ->expects($this->any())
            ->method('subject')
            ->will($this->returnValue($subject));

        $this->setReflectionClassInstance($listener);

        // assert success
        $event->subject->success = 1;
        $event->subject->created = 1;
        $event->subject->entity = new \stdClass();
        $event->subject->entity->id = 123;
        $this->assertTrue($this->callProtectedMethod('afterSave', [$event], $listener));

        // assert fails
        $event->subject->success = false;
        $event->subject->created = 1;
        $this->assertFalse($this->callProtectedMethod('afterSave', [$event], $listener));

        $event->subject->success = 1;
        $event->subject->created = false;
        $this->assertFalse($this->callProtectedMethod('afterSave', [$event], $listener));
    }

    /**
     * _getNewResourceUrl()
     *
     * @return void
     */
    public function testGetNewResourceUrl()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(null)
            ->enableOriginalConstructor()
            ->getMock();
        $controller->name = 'Countries';

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller', '_action'])
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $listener
            ->expects($this->any())
            ->method('_action')
            ->will($this->returnValue('add'));

        $this->setReflectionClassInstance($listener);

        $routerParameters = [
            'controller' => 'monkeys',
            'action' => 'view',
            123
        ];

        // assert Router defaults (to test against)
        $result = Router::url($routerParameters, true);
        $this->assertEquals('/monkeys/view/123', $result);

        // assert success
        $result = $this->callProtectedMethod('_getNewResourceUrl', ['monkeys', 123], $listener);
        $this->assertEquals('/monkeys/123', $result);
    }

    /**
     * Make sure render() works with find data
     *
     * @return void
     */
    public function testRenderWithResources()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(null)
            ->enableOriginalConstructor()
            ->getMock();
        $controller->name = 'Countries';
        $controller->Countries = TableRegistry::get('countries');

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller', '_action'])
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->getMock();
        $subject->entity = new Country();

        $listener->render($subject);
    }

    /**
     * Make sure render() works without find data
     *
     * @return void
     */
    public function testRenderWithoutResources()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(null)
            ->enableOriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller', '_action'])
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->getMock();

        $listener->render($subject);
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

        $this->assertTrue(class_exists('\Neomerx\JsonApi\Encoder\Encoder'));

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
            'Implement this test to bump coverage to 100%. Requires mocking system/php functions'
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
     * Make sure config option `jsonOptions` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `jsonOptions` only accepts an array
     */
    public function testValidateConfigOptionJsonOptionsFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'jsonOptions' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure config option `debugPrettyPrint` does not accept a string
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener configuration option `debugPrettyPrint` only accepts a boolean
     */
    public function testValidateConfigOptionDebugPrettyPrintFailWithString()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $listener->config([
            'debugPrettyPrint' => 'string-not-accepted'
        ]);

        $this->setReflectionClassInstance($listener);
        $this->callProtectedMethod('_validateConfigOptions', [], $listener);
    }

    /**
     * Make sure the listener accepts the correct request headers
     *
     * @return void
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
     * @expectedExceptionMessage JSON API requests require the "application/vnd.api+json" Accept header
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
     * @expectedExceptionMessage JSON API requests with data require the "application/vnd.api+json" Content-Type header
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
     * Make sure the listener does not accept the PUT method (since the JSON
     * API spec only supports PATCH)
     *
     * @expectedException \Cake\Network\Exception\BadRequestException
     * @expectedExceptionMessage JSON API does not support the PUT method, use PATCH instead
     */
    public function testCheckRequestMethodsFailOnPutMethod()
    {
        $request = new Request();
        $request->env('HTTP_ACCEPT', 'application/vnd.api+json');
        $request->env('REQUEST_METHOD', 'PUT');
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
     * @return void
     */
    public function testGetFindResult()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller'])
            ->getMock();

        $this->setReflectionClassInstance($listener);

        $subject = new Subject();
        $subject->entities = 'return-entities-property-from-subject-if-set';
        $result = $this->callProtectedMethod('_getFindResult', [$subject], $listener);
        $this->assertSame('return-entities-property-from-subject-if-set', $result);

        unset($subject->entities);

        $subject->entities = 'return-entity-property-from-subject-if-set';
        $result = $this->callProtectedMethod('_getFindResult', [$subject], $listener);
        $this->assertSame('return-entity-property-from-subject-if-set', $result);
    }

    /**
     * Make sure single/first entity is returned from subject based on action
     *
     * @return void
     */
    public function testGetSingleEntity()
    {
        $controller = $this
            ->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(null)
            ->enableOriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(['_controller', '_event'])
            ->getMock();

        $listener
            ->expects($this->any())
            ->method('_controller')
            ->will($this->returnValue($controller));

        $subject = $this
            ->getMockBuilder('\Crud\Event\Subject')
            ->getMock();

        $subject->entities = $this
            ->getMockBuilder('stdClass')
            ->disableOriginalConstructor()
            ->setMethods(['first'])
            ->getMock();

        $subject->entities
            ->expects($this->any())
            ->method('first')
            ->will($this->returnValue('return-first-entity-if-entities-property-is-set'));

        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getSingleEntity', [$subject], $listener);
        $this->assertSame('return-first-entity-if-entities-property-is-set', $result);

        unset($subject->entities);

        $subject->entity = 'return-entity-property-from-subject-if-set';
        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getSingleEntity', [$subject], $listener);
        $this->assertSame($subject->entity, $result);
    }

    /**
     * Make sure associations not present in the find result are stripped
     * from the AssociationCollection. In this test we will remove associated
     * model `Cultures`.
     *
     * @return void
     */
    public function testStripNonContainedAssociations()
    {
        $table = TableRegistry::get('Countries');
        $table->belongsTo('Currencies');
        $table->hasMany('Cultures');

        // make sure expected associations are there
        $associationsBefore = $table->associations();
        $this->assertNotEmpty($associationsBefore->get('currencies'));
        $this->assertNotEmpty($associationsBefore->get('cultures'));

        // make sure cultures are not present in the find result
        $query = $table->find()->contain([
            'Currencies'
        ]);
        $entity = $query->first();

        $this->assertNotEmpty($entity->currency);
        $this->assertNull($entity->cultures);

        // make sure cultures are removed from AssociationCollection
        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $associationsAfter = $this->callProtectedMethod('_stripNonContainedAssociations', [$table, $entity], $listener);

        $this->assertNotEmpty($associationsAfter->get('currencies'));
        $this->assertNull($associationsAfter->get('cultures'));
    }

    /**
     * Make sure we get a list of entity names for the current entity (name
     * passed as string) and all associated models.
     *
     * @return void
     */
    public function testGetEntityList()
    {
        $table = TableRegistry::get('Countries');
        $table->belongsTo('Currencies');
        $table->hasMany('Cultures');

        $associations = $table->associations();

        $this->assertNotEmpty($associations->get('currencies'));
        $this->assertNotEmpty($associations->get('cultures'));

        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $result = $this->callProtectedMethod('_getEntityList', ['Country', $associations], $listener);

        $expected = [
            'Country',
            'Currency',
            'Culture'
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * Test _checkRequestData()
     *
     * @return void
     */
    public function testCheckRequestData()
    {
        $requestData = null;

        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $this->assertNull($this->callProtectedMethod('_checkRequestData', [$requestData], $listener));
    }

    /**
     * Make sure arrays holding json_decoded JSON API data are properly
     * converted to CakePHP format.
     *
     * Make sure incoming JSON API data is transformed to CakePHP format.
     * Please note that data is already json_decoded by Crud here.
     *
     * @return void
     */
    public function testConvertJsonApiDataArray()
    {
        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);

        // assert success (single entity, no relationships)
        $jsonApiFixture = new File(Plugin::path('Crud') . 'tests' . DS . 'Fixture' . DS . 'JsonApi' . DS . 'post_country_no_relationships.json');
        $jsonApiArray = json_decode($jsonApiFixture->read(), true);
        $expected = [
            'code' => 'NL',
            'name' => 'The Netherlands'
        ];
        $result = $this->callProtectedMethod('_convertJsonApiDocumentArray', [$jsonApiArray], $listener);

        $this->assertSame($expected, $result);

        // assert success (single entity, multiple relationships)
        $jsonApiFixture = new File(Plugin::path('Crud') . 'tests' . DS . 'Fixture' . DS . 'JsonApi' . DS . 'post_country_multiple_relationships.json');
        $jsonApiArray = json_decode($jsonApiFixture->read(), true);
        $expected = [
            'code' => 'NL',
            'name' => 'The Netherlands',
            'culture_id' => '2',
            'currency_id' => '3'
        ];
        $result = $this->callProtectedMethod('_convertJsonApiDocumentArray', [$jsonApiArray], $listener);

        $this->assertSame($expected, $result);
    }
}
