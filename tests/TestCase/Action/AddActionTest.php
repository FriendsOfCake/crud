<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddActionTest extends IntegrationTestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = ['plugin.crud.blogs'];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Test the normal HTTP GET flow of _get
     *
     * @return void
     */
    public function testActionGet()
    {
        $this->get('/blogs/add');
        $result = $this->_response->body();

        $expected = '<legend>New Blog</legend>';
        $this->assertContains($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id"/>';
        $this->assertContains($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" maxlength="255" id="name"/>';
        $this->assertContains($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5"></textarea>';
        $this->assertContains($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test the normal HTTP GET flow of _get with query args
     *
     * Providing ?name=test should fill out the value in the 'name' input field
     *
     * @return void
     */
    public function testActionGetWithQueryArgs()
    {
        $this->get('/blogs/add?name=test');
        $result = $this->_response->body();

        $expected = '<legend>New Blog</legend>';
        $this->assertContains($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id"/>';
        $this->assertContains($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" maxlength="255" id="name" value="test"/>';
        $this->assertContains($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5"></textarea>';
        $this->assertContains($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test POST will create a record
     *
     * @return void
     */
    public function testActionPost()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);

        $this->assertRedirect('/blogs');
    }

    /**
     * Test POST will create a record and redirect to /blogs/add again
     * if _POST['_add'] is present
     *
     * @return void
     */
    public function testActionPostWithAddRedirect()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_add' => 1
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertRedirect('/blogs/add');
    }

    /**
     * Test POST will create a record and redirect to /blogs/edit/$id
     * if _POST['_edit'] is present
     *
     * @return void
     */
    public function testActionPostWithEditRedirect()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_edit' => 1
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertRedirect('/blogs/edit/6');
    }

    /**
     * Test POST with unsuccessful save()
     *
     * @return void
     */
    public function testActionPostErrorSave()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['save'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->once())
                    ->method('save')
                    ->will($this->returnValue(false));
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);
        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);
    }

    /**
     * Test POST with validation errors
     *
     * @return void
     */
    public function testActionPostValidationErrors()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);

        $expected = '<div class="error-message">Name need to be at least 10 characters long</div>';
        $this->assertContains($expected, $this->_response->body(), 'Could not find validation error in HTML');
    }

    /**
     * Data provider with GET and DELETE verbs
     *
     * @return array
     */
    public function apiGetHttpMethodProvider()
    {
        return [
            ['get'],
            ['delete']
        ];
    }

    /**
     * Test HTTP & DELETE verbs using API Listener
     *
     * @dataProvider apiGetHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiGet($method)
    {
        Router::scope('/', function ($routes) {
            $routes->extensions(['json']);
            $routes->fallbacks();
        });

        $this->{$method}('/Blogs/add.json');

        $this->assertResponseError();
        $this->assertResponseContains('Wrong request method');
    }

    /**
     * Data provider with PUT and POST verbs
     *
     * @return array
     */
    public function apiUpdateHttpMethodProvider()
    {
        return [
            ['put'],
            ['post']
        ];
    }

    /**
     * Test POST & PUT verbs using API Listener
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreate($method)
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');
                $this->_controller->RequestHandler->ext = 'json';
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => '6th blog post',
            'body' => 'Amazing blog post'
        ]);
        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertEquals(
            ['success' => true, 'data' => ['id' => 6]],
            json_decode($this->_response->body(), true)
        );
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation error
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreateError($method)
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short',
            'body' => 'Amazing blog post'
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('A validation error occurred');
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation errors
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreateErrors($method)
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->requirePresence('body')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short'
        ]);

        $this->assertResponseError();
        $this->assertResponseContains('2 validation errors occurred');
    }

    /**
     * Test the flow when the beforeSave event is stopped using the default
     * subject `success` state (false).
     *
     * @return void
     */
    public function testStopAddWithDefaultSubjectSuccess()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeSave', function ($event) {
                    $event->stopPropagation();
                });

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['add'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->never())
                    ->method('add');
            }
        );

        $this->post('/blogs/add');

        $this->assertEvents(['beforeSave', 'setFlash', 'beforeRedirect']);
        $this->assertFalse($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeSave event is stopped using manually
     * set non-default subject `success` state (true).
     *
     * @return void
     */
    public function testStopAddWithManuallySetSubjectSuccess()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
                    ->setMethods(['set'])
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeSave', function ($event) {
                    $event->stopPropagation();
                    $event->subject->success = true; // assert this
                });

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['add'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->never())
                    ->method('add');
            }
        );

        $this->post('/blogs/add');

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/blogs');
    }
}
