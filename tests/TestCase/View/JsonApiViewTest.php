<?php
namespace Crud\Test\TestCase\View;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Crud\Event\Subject;
use Crud\Listener\JsonApiListener;
use Crud\TestSuite\TestCase;
use Crud\View\JsonApiView;
use StdClass;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiViewTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.crud.countries',
        'plugin.crud.currencies',
        'plugin.crud.cultures',
    ];

    /**
     * Path to directory holding json fixtures with trailing slash
     *
     * @var
     */
    protected $_JsonDir;

    /**
     * Loaded with JsonApiListener default config settings on every setUp()
     *
     * @var array
     */
    protected $_defaultViewVars;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $listener = new JsonApiListener(new Controller());
        $this->_defaultViewVars = $listener->config();

        $this->_defaultViewVars = [
            '_urlPrefix' => $listener->config('urlPrefix'),
            '_withJsonApiVersion' => $listener->config('withJsonApiVersion'),
            '_meta' => $listener->config('meta'),
            '_include' => $listener->config('include'),
            '_fieldSets' => $listener->config('fieldSets'),
            '_jsonOptions' => $listener->config('jsonOptions'),
            '_debugPrettyPrint' => $listener->config('debugPrettyPrint'),
            '_debugQueryLog' => $listener->config('debugQueryLog'),
        ];

        // override some defaults to create more DRY tests
        $this->_defaultViewVars['_debugQueryLog'] = false;
        $this->_defaultViewVars['_jsonOptions'] = [JSON_PRETTY_PRINT];
        $this->_defaultViewVars['_serialize'] = true;

        // store path the the json fixtures
        $this->_JsonDir = Plugin::path('Crud') . 'tests' . DS . 'Fixture' . DS . 'JsonApi' . DS;
    }

    /**
     * Make sure we are testing with expected configuration settings.
     */
    public function testDefaultViewVars()
    {
        $expected = [
            '_urlPrefix' => null,
            '_withJsonApiVersion' => false,
            '_meta' => false,
            '_include' => [],
            '_fieldSets' => [],
            '_jsonOptions' => [
                JSON_PRETTY_PRINT
            ],
            '_debugPrettyPrint' => true,
            '_debugQueryLog' => false,
            '_serialize' => true
        ];
        $this->assertSame($expected, $this->_defaultViewVars);
    }

    /**
     * Helper function to easily create specific view for each test.
     *
     * @param string $tableName
     * @param array $viewVars
     * @return string NeoMerx jsonapi encoded array
     */
    protected function _getView($tableName, $viewVars)
    {
        // determine user configurable viewVars
        if (empty($viewVars)) {
            $viewVars = $this->_defaultViewVars;
        } else {
            $viewVars = array_replace_recursive($this->_defaultViewVars, $viewVars);
        }

        // create required (but non user configurable) viewVars next
        $request = new Request();
        $response = new Response();
        $controller = new Controller($request, $response);

        $controller->name = $tableName; // e.g. Countries
        $table = TableRegistry::get($tableName); // table object
        $entityName = Inflector::singularize($tableName); // e.g. Country

        // fetch data from test viewVar normally found in subject
        $subject = new Subject(new Event('Crud.beforeHandle'));
        $findResult = $viewVars[$table->table()];
        if (is_a($findResult, '\Cake\ORM\ResultSet')) {
            $subject->entities = $findResult;
        } else {
            $subject->entity = $findResult;
        }

        // create required '_entities' and '_associations' viewVars normally
        // produced and set by the JsonApiListener
        $listener = $this
            ->getMockBuilder('\Crud\Listener\JsonApiListener')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($listener);
        $entity = $this->callProtectedMethod('_getSingleEntity', [$subject], $listener);
        $associations = $this->callProtectedMethod('_stripAssociations', [$table, $entity], $listener);
        $entities = $this->callProtectedMethod('_getEntityList', [$entityName, $associations], $listener);

        $viewVars['_entities'] = $entities;
        $viewVars['_associations'] = $associations;

        // set viewVars before creating the view
        $controller->set($viewVars);
        $builder = $controller->viewBuilder();
        $builder->className('\Crud\View\JsonApiView');

        return $controller->createView();
    }

    /**
     * Make sure an exception is thrown when required viewVar is missing
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener required viewVar '_entities' is not set
     */
    public function testEntitiesToNeoMerxSchemaMissingEntitiesException()
    {
        $view = new JsonApiView();
        $view->render();
    }

    /**
     * Make sure an exception is thrown when requesting an unloaded Entity class
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage JsonApiListener cannot not find Entity class UnknownOrUnloadedEntity
     */
    public function testEntitiesToNeoMerxSchemaUnloadedClassException()
    {
        $view = new JsonApiView();
        $this->setReflectionClassInstance($view);
        $parameters = [
            'UnknownOrUnloadedEntity'
        ];
        $this->callProtectedMethod('_entitiesToNeoMerxSchema', [$parameters], $view);
    }

    /**
     * Make sure Crud's DynamicEntitySchema produces expected json.
     *
     * Please note that we are deliberately using assertSame() instead of
     * assertJsonFileEqualsJsonFile() for all tests because the latter will
     * ignore formatting like e.g. JSON_PRETTY_PRINT.
     *
     * @return void
     */
    public function testDynamicEntitySchema()
    {
        // test collection of entities without relationships
        $countries = TableRegistry::get('Countries')->find()->all();
        $view = $this->_getView('Countries', [
            'countries' => $countries
        ]);

        $this->assertSame(
            (new File($this->_JsonDir . 'get_countries_no_relationships.json'))->read(),
            $view->render()
        );

        // test single entity without relationships
        $countries = TableRegistry::get('Countries')->find()->first();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
        ]);

        $this->assertSame(
            (new File($this->_JsonDir . 'get_country_no_relationships.json'))->read(),
            $view->render()
        );
    }

    /**
     * Make sure user option `withJsonVersion` produces expected json
     *
     * @return void
     */
    public function testOptionalWithJsonApiVersion()
    {
        // make sure top-level node is added when true
        $countries = TableRegistry::get('Countries')->find()->all();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_withJsonApiVersion' => true
        ]);
        $expectedVersionArray = [
            'jsonapi' => [
                'version' => '1.0'
            ]
        ];

        $this->assertArraySubset($expectedVersionArray, json_decode($view->render(), true));

        // make sure top-level node is added when passed an array
        $countries = TableRegistry::get('Countries')->find()->all();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_withJsonApiVersion' => [
                'meta-key-1' => 'meta-val-1',
                'meta-key-2' => 'meta-val-2',
            ]
        ]);
        $expectedVersionArray = [
            'jsonapi' => [
                'version' => '1.0',
                'meta' => [
                    'meta-key-1' => 'meta-val-1',
                    'meta-key-2' => 'meta-val-2',
                ]
            ]
        ];

        $this->assertArraySubset($expectedVersionArray, json_decode($view->render(), true));

        // make sure top-level node is not added when false
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_withJsonApiVersion' => false,
        ]);
        $this->assertArrayNotHasKey('jsonapi', json_decode($view->render(), true));
    }

    /**
     * Make sure user option `meta` produces expected json
     *
     * @return void
     */
    public function testOptionalMeta()
    {
        // make sure top-level node is added when passed an array
        $countries = TableRegistry::get('Countries')->find()->all();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_meta' => [
                'author' => 'bravo-kernel'
            ],
        ]);
        $expectedMetaArray = [
            'meta' => [
                'author' => 'bravo-kernel'
            ]
        ];

        $this->assertArraySubset($expectedMetaArray, json_decode($view->render(), true));

        // make sure top-level node is not added when false
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_meta' => false,
        ]);
        $this->assertArrayNotHasKey('meta', json_decode($view->render(), true));

        // make sure we can also generate a response with just/only a meta
        // node (as supported by the jsonapi spec)
        $view = $this->_getView('Countries', [
            'countries' => null,
            '_meta' => [
                'author' => 'bravo-kernel'
            ],
        ]);
        $expectedResponseArray = [
            'meta' => [
                'author' => 'bravo-kernel'
            ]
        ];

        $this->assertSame($expectedResponseArray, json_decode($view->render(), true));
    }

    /**
     * Make sure user option `debugQueryLog` produces expected json
     *
     * @return void
     */
    public function testOptionalDebugQueryLog()
    {
        // make sure top-level node is added when true
        $this->assertTrue(Configure::read('debug'));
        $countries = TableRegistry::get('Countries')->find()->all();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_debugQueryLog' => true,
            '_queryLogs' => 'normally-populated-by-QueryLogTrait'
        ]);
        $expectedMetaArray = [
            'query' => 'normally-populated-by-QueryLogTrait'
        ];

        $this->assertArraySubset($expectedMetaArray, json_decode($view->render(), true));

        // make sure top-level node is not added when false
        $this->assertTrue(Configure::read('debug'));
        $view = $this->_getView('Countries', [
            'countries' => $countries
        ]);

        $this->assertArrayNotHasKey('query', json_decode($view->render(), true));
    }

    /**
     * Make sure user option `debugPrettyPrint` behaves produces expected json
     *
     * @return void
     */
    public function testOptionalDebugPrettyPrint()
    {
        // make sure pretty json is generated when true AND in debug mode
        $this->assertTrue(Configure::read('debug'));
        $countries = TableRegistry::get('Countries')->find()->first();
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_debugPrettyPrint' => true,
        ]);

        $this->assertSame(
            (new File($this->_JsonDir . 'get_country_no_relationships.json'))->read(),
            $view->render()
        );

        // make sure we can produce non-pretty in debug mode as well
        $this->assertTrue(Configure::read('debug'));
        $view = $this->_getView('Countries', [
            'countries' => $countries,
            '_debugPrettyPrint' => false,
            '_jsonOptions' => 0,
        ]);

        $this->assertSame(
            '{"data":{"type":"countries","id":"1","attributes":{"code":"NL","name":"The Netherlands","currency_id":1},"links":{"self":"\/countries\/1"}}}',
            $view->render()
        );
    }


    /**
     * Make sure correct $data to be encoded is fetched from set viewVars

     *
     * @return void
     */
    public function testGetDataToSerializeFromViewVarsSuccess()
    {
        $view = $this
            ->getMockBuilder('\Crud\View\JsonApiView')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($view);

        // make sure set expected data is returned when _serialize is true
        $view->viewVars = [
            'countries' => 'dummy-would-normally-be-an-entity-or-resultset',
        ];

        $this->assertSame(
            'dummy-would-normally-be-an-entity-or-resultset',
            $this->callProtectedMethod('_getDataToSerializeFromViewVars', [], $view)
        );

        // make sure null is returned when no data is found (which would mean
        // only _specialVars were set and since these are all flipped it leads
        // to a null result)
        $view->viewVars = [
            '_meta' => false
            ];
        $this->assertNull($this->callProtectedMethod('_getDataToSerializeFromViewVars', [], $view));

        // When passing an array as _serialize ONLY the first entity in that
        // array list will be used to return the corresponding viewar as $data.
        $view->viewVars = [
            'countries' => 'dummy-country-would-normally-be-an-entity-or-resultset',
            'currencies' => 'dummy-currency-would-normally-be-an-entity-or-resultset',
        ];

        $parameters = [
            'countries',
            'currencies'
        ];

        $this->assertSame(
            'dummy-country-would-normally-be-an-entity-or-resultset',
            $this->callProtectedMethod('_getDataToSerializeFromViewVars', [$parameters], $view)
        );

        // In this case the first entity in the _serialize array does not have
        // a corresponding viewVar so null will be returned as data.
        $view->viewVars = [
            'currencies' => 'dummy-currency-would-normally-be-an-entity-or-resultset',
        ];

        $parameters = [
            'countries',
            'currencies'
        ];

        $this->assertNull($this->callProtectedMethod('_getDataToSerializeFromViewVars', [$parameters], $view));
    }

    /**
     * Make sure `_serialize` will not accept an object
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage Assigning an object to JsonApiListener "_serialize" is deprecated, assign the object to its own variable and assign "_serialize" = true instead.
     */
    public function testGetDataToSerializeFromViewVarsObjectExcecption()
    {
        $view = $this
            ->getMockBuilder('\Crud\View\JsonApiView')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($view);
        $this->callProtectedMethod('_getDataToSerializeFromViewVars', [new StdClass()], $view);
    }

    /**
     * Make sure we are producing the right jsonOptions
     *
     * @return void
     */
    public function testJsonOptions()
    {
        $view = $this
            ->getMockBuilder('\Crud\View\JsonApiView')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($view);

        // test debug mode with `debugPrettyPrint` option disabled
        $this->assertTrue(Configure::read('debug'));
        $view->viewVars = [
            '_debugPrettyPrint' => false,
            '_jsonOptions' => [
                JSON_HEX_AMP, // 2
                JSON_HEX_QUOT, // 8
            ]
        ];
        $this->assertEquals(10, $this->callProtectedMethod('_jsonOptions', [], $view));

        // test debug mode with `debugPrettyPrint` option enabled
        $this->assertTrue(Configure::read('debug'));
        $view->viewVars = [
            '_debugPrettyPrint' => true, //128
            '_jsonOptions' => [
                JSON_HEX_AMP, // 2
                JSON_HEX_QUOT, // 8
            ]
        ];
        $this->assertEquals(138, $this->callProtectedMethod('_jsonOptions', [], $view));

        // test production mode with `debugPrettyPrint` option disabled
        Configure::write('debug', false);
        $this->assertFalse(Configure::read('debug'));
        $view->viewVars = [
            '_debugPrettyPrint' => false,
            '_jsonOptions' => [
                JSON_HEX_AMP, // 2
                JSON_HEX_QUOT, // 8
            ]
        ];
        $this->assertEquals(10, $this->callProtectedMethod('_jsonOptions', [], $view));

        // test production mode with `debugPrettyPrint` option enabled
        $this->assertFalse(Configure::read('debug'));
        $view->viewVars = [
            '_debugPrettyPrint' => true,
            '_jsonOptions' => [
                JSON_HEX_AMP, // 2
                JSON_HEX_QUOT, // 8
            ]
        ];
        $this->assertEquals(10, $this->callProtectedMethod('_jsonOptions', [], $view));
    }
}
