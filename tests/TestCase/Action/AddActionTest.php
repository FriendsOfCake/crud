<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

use Cake\Controller\Component\FlashComponent;
use Cake\Core\Configure;
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
    protected array $fixtures = ['plugin.Crud.Blogs'];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public string $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Test the normal HTTP GET flow of _get
     *
     * @return void
     */
    public function testActionGet()
    {
        $this->get('/blogs/add');
        $result = (string)$this->_response->getBody();

        $expected = '<legend>New Blog</legend>';
        $this->assertStringContainsString($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id">';
        $this->assertStringContainsString($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" id="name" maxlength="255">';
        $this->assertStringContainsString($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5"></textarea>';
        $this->assertStringContainsString($expected, $result, '"body" do not match the expected value');
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
        $result = (string)$this->_response->getBody();

        $expected = '<legend>New Blog</legend>';
        $this->assertStringContainsString($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id">';
        $this->assertStringContainsString($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" id="name" value="test" maxlength="255">';
        $this->assertStringContainsString($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5"></textarea>';
        $this->assertStringContainsString($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test POST will create a record
     *
     * @return void
     */
    public function testActionPost()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_add' => 1,
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_edit' => 1,
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['save'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );
                $blogs
                    ->expects($this->once())
                    ->method('save')
                    ->willReturn(false);

                $this->getTableLocator()->set('Blogs', $blogs);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs
                    ->getValidator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ],
                    ]);
            }
        );

        $this->post('/blogs/add', [
            'name' => 'Hello',
            'body' => 'Pretty hot body',
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);

        if (version_compare(Configure::version(), '4.3.0', '>=')) {
            $expected = '<div class="error-message" id="name-error">Name need to be at least 10 characters long</div>';
        } else {
            $expected = '<div class="error-message">Name need to be at least 10 characters long</div>';
        }
        $this->assertStringContainsString(
            $expected,
            (string)$this->_response->getBody(),
            'Could not find validation error in HTML'
        );
    }

    /**
     * Data provider with GET and DELETE verbs
     *
     * @return array
     */
    public static function apiGetHttpMethodProvider()
    {
        return [
            ['get'],
            ['delete'],
        ];
    }

    /**
     * Test HTTP & DELETE verbs using API Listener
     *
     * @param  string $method
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('apiGetHttpMethodProvider')]
    public function testApiGet($method)
    {
        Router::createRouteBuilder('/')
            ->setExtensions(['json'])
            ->fallbacks();

        $this->{$method}('/Blogs/add.json');

        $this->assertResponseError();
        $this->assertResponseContains('Method Not Allowed');
    }

    /**
     * Data provider with PUT and POST verbs
     *
     * @return array
     */
    public static function apiUpdateHttpMethodProvider()
    {
        return [
            ['put'],
            ['post'],
        ];
    }

    /**
     * Test POST & PUT verbs using API Listener
     *
     * @param  string $method
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('apiUpdateHttpMethodProvider')]
    public function testApiCreate($method)
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->never())
                    ->method('set');

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => '6th blog post',
            'body' => 'Amazing blog post',
        ]);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertEquals(
            ['success' => true, 'data' => ['id' => 6]],
            json_decode((string)$this->_response->getBody(), true)
        );
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation error
     *
     * @param  string $method
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('apiUpdateHttpMethodProvider')]
    public function testApiCreateError($method)
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                if (get_class($this->_controller) === 'Cake\Controller\ErrorController') {
                    return;
                }

                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->never())
                    ->method('set');

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->getValidator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ],
                    ]);
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short',
            'body' => 'Amazing blog post',
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('A validation error occurred');
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation errors
     *
     * @param  string $method
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('apiUpdateHttpMethodProvider')]
    public function testApiCreateErrors($method)
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                if (get_class($this->_controller) === 'Cake\Controller\ErrorController') {
                    return;
                }

                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->never())
                    ->method('set');

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->getValidator()
                    ->requirePresence('name')
                    ->requirePresence('body')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ],
                    ]);
            }
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short',
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeSave', function ($event) {
                    $event->stopPropagation();
                });

                $model = $this->getMockForModel(
                    $this->tableClass,
                    [],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->getTableLocator()->set('Blogs', $model);
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->_controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeSave', function ($event) {
                    $event->stopPropagation();
                    $event->getSubject()->success = true; // assert this
                });

                $model = $this->getMockForModel(
                    $this->tableClass,
                    [],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->getTableLocator()->set('Blogs', $model);
            }
        );

        $this->post('/blogs/add');

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/blogs');
    }
}
