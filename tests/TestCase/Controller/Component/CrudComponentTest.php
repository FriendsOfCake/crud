<?php
declare(strict_types=1);

namespace Crud\TestCase\Controller\Crud;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Crud\Controller\Component\CrudComponent;
use Crud\Test\App\Controller\Component\TestCrudComponent;
use Crud\Test\App\Controller\CrudExamplesController;
use Crud\Test\App\Event\TestCrudEventManager;
use Crud\Test\App\Listener\TestListener;
use Crud\TestSuite\TestCase;
use Exception;

/**
 * CrudComponentTestCase
 */
class CrudComponentTest extends TestCase
{
    /**
     * Fixtures
     *
     * Use the core posts fixture to have something to work on.
     * What fixture is used is almost irrelevant, was chosen as it is simple
     */
    protected array $fixtures = [
        'core.Posts',
    ];

    /**
     * setUp
     *
     * Setup the classes the crud component needs to be testable
     */
    public function setUp(): void
    {
        parent::setUp();

        EventManager::instance(new TestCrudEventManager());

        $this->model = $this->getTableLocator()->get('CrudExamples');

        $this->request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['is', 'getMethod'])
            ->getMock()
            ->withParam('action', 'index');

        $this->request->expects($this->any())->method('is')->will($this->returnValue(true));

        $this->controller = $this->getMockBuilder(CrudExamplesController::class)
            ->onlyMethods(['redirect', 'render'])
            ->setConstructorArgs([$this->request, 'CrudExamples', EventManager::instance()])
            ->getMock();
        $this->controller->defaultTable = 'CrudExamples';

        $this->Registry = $this->controller->components();

        $config = [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
            ],
        ];

        $this->Crud = new TestCrudComponent($this->Registry, $config);
        $this->Crud->beforeFilter(new Event('Controller.beforeFilter'));
        $this->controller->Crud = $this->Crud;
    }

    /**
     * tearDown method
     */
    public function tearDown(): void
    {
        unset(
            $this->model,
            $this->request,
            $this->controller,
            $this->Crud,
            $this->Registry
        );

        parent::tearDown();
    }

    /**
     * Test config normalization
     *
     * @return void
     */
    public function testConfigNormalization()
    {
        $config = [
            'actions' => [
                'Crud.Index',
                'add' => 'Crud.Add',
                'view' => ['className' => 'Crud.View', 'viewVar' => 'beers'],
            ],
            'listeners' => [
                'Crud.Related',
            ],
        ];
        $Crud = $this->getMockBuilder(CrudComponent::class)
            ->onlyMethods(['_loadListeners', 'trigger'])
            ->setConstructorArgs([$this->Registry, $config])
            ->getMock();
        $Crud
            ->expects($this->once())
            ->method('_loadListeners');
        $Crud
            ->expects($this->once())
            ->method('trigger');
        $Crud->beforeFilter(new Event('Controller.beforeFilter'));

        $expected = [
            'index' => ['className' => 'Crud.Index'],
            'add' => ['className' => 'Crud.Add'],
            'view' => ['className' => 'Crud.View', 'viewVar' => 'beers'],
        ];
        $this->assertEquals($expected, $Crud->getConfig('actions'));

        $expected = [
            'related' => ['className' => 'Crud.Related'],
        ];
        $this->assertEquals($expected, $Crud->getConfig('listeners'));
    }

    /**
     * testDefaultActionSetting
     *
     * @see https://github.com/FriendsOfCake/crud/pull/534
     * @return void
     */
    public function testDefaultActionSetting()
    {
        $config = [
            'actions' => [
                'Crud.Index',
            ],
        ];

        $this->request = $this->request->withParam('action', 'index');

        $Crud = new CrudComponent($this->Registry, $config);

        $this->assertTrue($Crud->isActionMapped('index'));
    }

    /**
     * Test deprecated `executeAction` calls `execute` correctly
     */
    public function testExecuteActionToExecute()
    {
        $config = ['actions' => ['Crud.Index']];

        $Crud = $this->getMockBuilder(CrudComponent::class)
            ->onlyMethods(['execute'])
            ->setConstructorArgs([$this->Registry, $config])
            ->getMock();
        $Crud
            ->expects($this->once())
            ->method('execute')
            ->with('index', ['foo' => 'bar']);

        $Crud->execute('index', ['foo' => 'bar']);
    }

    /**
     * testEnable
     */
    public function testEnable()
    {
        $this->Crud->mapAction('puppies', 'Crud.View', false);
        $this->Crud->enable('puppies');

        $result = $this->Crud->isActionMapped('puppies');
        $this->assertTrue($result);
    }

    /**
     * testDisableAction
     */
    public function testDisableAction()
    {
        $this->Crud->disable('view');

        $result = $this->Crud->isActionMapped('view');
        $this->assertFalse($result);
    }

    /**
     * testMapAction
     */
    public function testMapAction()
    {
        $this->Crud->mapAction('puppies', 'Crud.View');

        $result = $this->Crud->isActionMapped('puppies');
        $this->assertTrue($result);

        $this->Crud->mapAction('kittens', [
            'className' => 'Crud.Index',
            'relatedModels' => false,
        ]);

        $result = $this->Crud->isActionMapped('kittens');
        $this->assertTrue($result);

        $expected = [
            'className' => 'Crud.Index',
            'relatedModels' => false,
        ];
        $this->assertEquals($expected, $this->Crud->getConfig('actions.kittens'));
    }

    /**
     * testView
     */
    public function testView()
    {
        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->controller
            ->expects($this->once())
            ->method('render');

        $this->Crud->view('view', 'cupcakes');
        $this->Crud->execute('view', ['1']);
    }

    /**
     * testIsActionMappedYes
     */
    public function testIsActionMappedYes()
    {
        $result = $this->Crud->isActionMapped('index');
        $this->assertTrue($result);

        $request = $this->controller->getRequest()->withParam('action', 'edit');
        $this->controller->setRequest($request);
        $this->Crud->initialize([]);
        $result = $this->Crud->isActionMapped();
        $this->assertTrue($result);
    }

    /**
     * testIsActionMappedNo
     */
    public function testIsActionMappedNo()
    {
        $result = $this->Crud->isActionMapped('puppies');
        $this->assertFalse($result);

        $config = [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
            ],
        ];

        $request = $this->controller->getRequest()->withParam('action', 'rainbows');
        $this->controller->setRequest($request);

        $this->Crud = new TestCrudComponent($this->Registry, $config);
        $this->Crud->beforeFilter(new Event('Controller.beforeFilter'));
        $this->controller->Crud = $this->Crud;

        $this->Crud->beforeFilter(new Event('Controller.beforeFilter'));
        $result = $this->Crud->isActionMapped();
        $this->assertFalse($result);
    }

    /**
     * Tests on method registers an event
     */
    public function testOn()
    {
        $callback = function () {
        };
        $this->Crud->on('event', $callback);

        $return = $this->controller->getEventManager()->listeners('Crud.event');

        $expected = [
            [
                'callable' => $callback,
            ],
        ];
        $this->assertSame($expected, $return);
    }

    /**
     * tests on method registers an event with extra params
     */
    public function testOnWithPriPriority()
    {
        $one = function () {
        };
        $two = function () {
        };
        $three = function () {
        };
        $this->Crud->on('event', $one);
        $this->Crud->on('event', $two, ['priority' => 1]);
        $this->Crud->on('event', $three, ['priority' => 99999]);

        $return = $this->controller->getEventManager()->listeners('Crud.event');

        $expected = [
            [
                'callable' => $two,
            ],
            [
                'callable' => $one,
            ],
            [
                'callable' => $three,
            ],
        ];
        $this->assertSame($expected, $return);
    }

    /**
     * Test if crud complains about unmapped actions
     *
     * @return void
     */
    public function testCrudWillComplainAboutUnmappedAction()
    {
        $this->expectException(Exception::class);

        $this->Crud->execute('show_all');
    }

    /**
     * Test if view with array yields the expected result
     *
     * @return void
     */
    public function testViewWithArrayNewAction()
    {
        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with('index');

        $this->Crud->mapAction('show_all', ['className' => 'Crud.index']);
        $this->Crud->view(['show_all' => 'index', 'index' => 'overview']);

        $this->Crud->execute('showAll');
    }

    /**
     * Test if view with array yields the expected result
     *
     * @return void
     */
    public function testViewWithArrayIndexAction()
    {
        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with('overview');

        $this->Crud->mapAction('show_all', ['className' => 'Crud.index']);
        $this->Crud->view(['show_all' => 'index', 'index' => 'overview']);

        $this->Crud->execute('index');
    }

    /**
     * Test that having no mapped model for an action,
     * just use the modelClass from the controller
     *
     * @return void
     */
    public function testSetModelPropertiesDefault()
    {
        $this->Crud->setAction('index');
        $this->assertNull($this->Crud->getModelName());
    }

    /**
     * testMappingNonExistentAction
     *
     * @return void
     */
    public function testMappingNonExistentAction()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find action class: Sample.Index');

        $this->Crud->mapAction('test', 'Sample.Index');
    }

    /**
     * Test that having a 'search' action in the controller
     * and calling ->execute('index') will still
     * render the 'search' view
     *
     * @return void
     */
    public function testViewCanBeChangedInControllerAction()
    {
        $request = $this->request->withParam('action', 'search');

        $request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->controller->setRequest($request);

        $this->controller
            ->expects($this->once())
            ->method('render')
            ->with('search');

        $this->controller->search();
    }

    /**
     * Test the default configuration for CrudComponent
     *
     * @return void
     */
    public function testDefaultConfig()
    {
        $Crud = new CrudComponent($this->Registry);

        $result = $Crud->getConfig();
        $expected = [
            'actions' => [],
            'eventPrefix' => 'Crud',
            'listeners' => [],
            'messages' => [
                'domain' => 'crud',
                'invalidId' => [
                    'code' => 400,
                    'class' => BadRequestException::class,
                    'text' => 'Invalid id',
                ],
                'recordNotFound' => [
                    'code' => 404,
                    'class' => NotFoundException::class,
                    'text' => 'Not found',
                ],
                'badRequestMethod' => [
                    'code' => 405,
                    'class' => MethodNotAllowedException::class,
                    'text' => 'Method not allowed. This action permits only {methods}',
                ],
            ],
            'eventLogging' => false,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that providing configuration for a new
     * listener in the Crud setting should preserve
     * the defaults and add the new listener to the array
     *
     * @return void
     */
    public function testConstructMerging()
    {
        $config = [
            'listeners' => [
                'api' => 'Crud.Api',
            ],
        ];

        $Crud = new CrudComponent($this->Registry, $config);
        $result = $Crud->getConfig();
        $expected = [
            'actions' => [],
            'eventPrefix' => 'Crud',
            'listeners' => [
                'api' => ['className' => 'Crud.Api'],
            ],
            'messages' => [
                'domain' => 'crud',
                'invalidId' => [
                    'code' => 400,
                    'class' => BadRequestException::class,
                    'text' => 'Invalid id',
                ],
                'recordNotFound' => [
                    'code' => 404,
                    'class' => NotFoundException::class,
                    'text' => 'Not found',
                ],
                'badRequestMethod' => [
                    'code' => 405,
                    'class' => MethodNotAllowedException::class,
                    'text' => 'Method not allowed. This action permits only {methods}',
                ],
            ],
            'eventLogging' => false,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that providing configuration for a new
     * listener in the Crud setting should preserve
     * the defaults and add the new listener to the array
     *
     * @return void
     */
    public function testConstructMerging2()
    {
        $config = [
            'listeners' => [
            ],
        ];

        $Crud = new CrudComponent($this->Registry, $config);
        $result = $Crud->getConfig();
        $expected = [
            'actions' => [],
            'eventPrefix' => 'Crud',
            'listeners' => [],
            'messages' => [
                'domain' => 'crud',
                'invalidId' => [
                    'code' => 400,
                    'class' => BadRequestException::class,
                    'text' => 'Invalid id',
                ],
                'recordNotFound' => [
                    'code' => 404,
                    'class' => NotFoundException::class,
                    'text' => 'Not found',
                ],
                'badRequestMethod' => [
                    'code' => 405,
                    'class' => MethodNotAllowedException::class,
                    'text' => 'Method not allowed. This action permits only {methods}',
                ],
            ],
            'eventLogging' => false,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that addListener works - without listener
     * default config
     *
     * @return void
     */
    public function testAddListenerWithoutDefaults()
    {
        $listeners = $this->Crud->getConfig('listeners');
        $expected = [];

        $this->assertEquals($expected, $listeners);

        $this->Crud->addListener('api', 'Crud.Api');

        $listeners = $this->Crud->getConfig('listeners');
        $expected = [
            'api' => ['className' => 'Crud.Api'],
        ];
        $this->assertEquals($expected, $listeners);

        $this->assertEquals(
            ['className' => 'Crud.Api'],
            $this->Crud->defaults('listeners', 'api')
        );
    }

    /**
     * Test that addListener works - with listener
     * default config
     *
     * @return void
     */
    public function testAddListenerWithDefaults()
    {
        $this->Crud->addListener('api', 'Crud.Api', ['test' => 1]);

        $listeners = $this->Crud->getConfig('listeners');
        $expected = [
            'api' => ['className' => 'Crud.Api', 'test' => 1],
        ];
        $this->assertEquals($expected, $listeners);

        $this->assertEquals(
            ['className' => 'Crud.Api', 'test' => 1],
            $this->Crud->defaults('listeners', 'api')
        );
    }

    /**
     * Test that removeListener works
     *
     * @return void
     */
    public function testRemoveListener()
    {
        $this->Crud->addListener('api', 'Crud.Api');
        $listeners = $this->Crud->getConfig('listeners');
        $expected = [
            'api' => ['className' => 'Crud.Api'],
        ];
        $this->assertEquals($expected, $listeners);

        $this->Crud->removeListener('api');
        $listeners = $this->Crud->getConfig('listeners');
        $this->assertEquals([], $listeners);

        // Should now throw an exception
        $this->expectException('Exception', 'Listener "api" is not configured');
        $this->Crud->listener('api');
    }

    /**
     * Test removing a listener that doesn't exist
     * should return false
     *
     * @return void
     */
    public function testRemoveListenerNoExist()
    {
        $this->assertFalse($this->Crud->removeListener('invalid_name'));
    }

    /**
     * Test changing view var for one action works
     *
     * @return void
     */
    public function testViewVarSingleAction()
    {
        $this->Crud->viewVar('index', 'my_var');

        $expected = 'my_var';
        $result = $this->Crud->action('index')->viewVar();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test changing view var for multiple actions works
     *
     * @return void
     */
    public function testViewVarMultipleActions()
    {
        $this->Crud->viewVar(['index' => 'my_var', 'view' => 'view_var']);

        $expected = 'my_var';
        $result = $this->Crud->action('index')->viewVar();
        $this->assertEquals($expected, $result);

        $expected = 'view_var';
        $result = $this->Crud->action('view')->viewVar();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test changing view var for multiple actions works
     *
     * @return void
     */
    public function testFindMethodMultipleActions()
    {
        $this->Crud->findMethod(['index' => 'my_all', 'view' => 'my_view']);

        $expected = 'my_all';
        $result = $this->Crud->action('index')->findMethod();
        $this->assertEquals($expected, $result);

        $expected = 'my_view';
        $result = $this->Crud->action('view')->findMethod();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test setting defaults for one action works
     *
     * @return void
     */
    public function testDefaultsOnAction()
    {
        $this->Crud->defaults('actions', 'index', ['unit_test' => true]);
        $config = $this->Crud->defaults('actions', 'index');

        $this->assertTrue($config['unit_test']);
    }

    /**
     * Test setting defaults for multiple actions work
     *
     * @return void
     */
    public function testDefaultsMultipleActions()
    {
        $this->Crud->defaults('actions', ['index', 'view'], ['unit_test' => true]);

        $config = $this->Crud->defaults('actions', 'index');
        $this->assertTrue($config['unit_test']);

        $config = $this->Crud->defaults('actions', 'view');
        $this->assertTrue($config['unit_test']);
    }

    /**
     * Test setting defaults for one listener works
     *
     * @return void
     */
    public function testDefaultsOneListener()
    {
        $this->Crud->defaults('listeners', 'translations', ['unit_test' => true]);
        $config = $this->Crud->defaults('listeners', 'translations');

        $this->assertTrue($config['unit_test']);
    }

    /**
     * Test setting defaults for multiple actions work
     *
     * @return void
     */
    public function testDefaultsMultipleListeners()
    {
        $this->Crud->defaults(
            'listeners',
            ['translations', 'relatedModels'],
            ['unit_test' => true]
        );

        $config = $this->Crud->defaults('listeners', 'translations');
        $this->assertTrue($config['unit_test']);

        $config = $this->Crud->defaults('listeners', 'relatedModels');
        $this->assertTrue($config['unit_test']);
    }

    /**
     * Test setting defaults for one listener works
     *
     * This proves that not setting 'className' doesn't break
     *
     * @return void
     */
    public function testDefaultsListenerNotAlreadyLoaded()
    {
        $this->Crud->defaults('listeners', 'api', ['unit_test' => true]);
        $config = $this->Crud->defaults('listeners', 'api');
        $this->assertTrue($config['unit_test']);
    }

    /**
     * Test adding a listener only by class name
     *
     * @return void
     */
    public function testAddListenerOnlyClassName()
    {
        $this->Crud->addListener('Crud.api');
        $config = $this->Crud->getConfig('listeners');
        $this->assertEquals(['className' => 'Crud.Api'], $config['api']);
    }

    /**
     * Test adding a listener by name and class name
     *
     * @return void
     */
    public function testAddListenerByNameAndClassName()
    {
        $this->Crud->addListener('foo', 'Crud.Api');
        $config = $this->Crud->getConfig('listeners');
        $this->assertEquals(['className' => 'Crud.Api'], $config['foo']);
    }

    /**
     * Test that it's possible to change just one sub key
     * by providing all the parents, without loosing any
     * default settings
     *
     * @return void
     */
    public function testConfigMergeWorks()
    {
        $this->Crud->setConfig(['messages' => ['invalidId' => ['code' => 500]]]);

        $expected = [
            'code' => 500,
            'class' => BadRequestException::class,
            'text' => 'Invalid id',
        ];
        $result = $this->Crud->getConfig('messages.invalidId');
        $this->assertEquals($expected, $result);
    }

    /**
     * Using $key and value, and specifying no merge should overwrite the value keys
     *
     * @return void
     */
    public function testConfigOverwrite()
    {
        $this->Crud->setConfig('messages.invalidId', ['code' => 500], false);

        $expected = [
            'domain' => 'crud',
            'invalidId' => [
                'code' => 500,
            ],
            'recordNotFound' => [
                'code' => 404,
                'class' => NotFoundException::class,
                'text' => 'Not found',
            ],
            'badRequestMethod' => [
                'code' => 405,
                'class' => MethodNotAllowedException::class,
                'text' => 'Method not allowed. This action permits only {methods}',
            ],
        ];
        $result = $this->Crud->getConfig('messages');
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests that is possible to set the model class to use for the action
     *
     * @return void
     */
    public function testUseModel()
    {
        $this->Crud = new CrudComponent($this->Registry, ['actions' => ['Crud.Index']]);
        $this->Crud->beforeFilter(new Event('Controller.beforeFilter'));
        $this->controller->Crud = $this->Crud;
        $class = $this->getMockClass('Model');
        $this->Crud->useModel($class);

        $this->assertEquals($class, $this->Crud->table()->getAlias());
    }

    /**
     * testLoadListener
     *
     * @return void
     */
    public function testLoadListener()
    {
        $this->Crud->setConfig('listeners.HasSetup', [
            'className' => TestListener::class,
        ]);

        $this->setReflectionClassInstance($this->Crud);
        $listener = $this->callProtectedMethod('_loadListener', ['HasSetup'], $this->Crud);
        $this->assertSame(1, $listener->callCount, 'Setup should be called');
    }
}
