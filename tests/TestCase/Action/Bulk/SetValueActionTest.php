<?php
namespace Crud\Test\TestCase\Action\Bulk;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SetValueActionTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.crud.blogs',
        'plugin.crud.users'
    ];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Data provider with all HTTP verbs
     *
     * @return array
     */
    public function allHttpMethodProvider()
    {
        return [
            ['post'],
            ['put'],
        ];
    }

    /**
     * Test the normal HTTP flow for all HTTP verbs
     *
     * @dataProvider allHttpMethodProvider
     * @return void
     */
    public function testAllRequestMethods($method)
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
                        'Set value successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Set value successfully'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->{$method}('/blogs/deactivateAll', [
            'id' => [
                1,
                2,
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'afterBulk', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeBulk event is stopped
     *
     * @return void
     */
    public function testStopBeforeBulk()
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
                        'Could not set value',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not set value'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeBulk', function ($event) {
                    $event->stopPropagation();
                });
            }
        );

        $this->post('/blogs/deactivateAll', [
            'id' => [
                1,
                2,
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'setFlash', 'beforeRedirect']);
        $this->assertFalse($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test with UUID request data.
     *
     * @return void
     */
    public function testUuidRequestData()
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
                        'Set value successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Set value successfully'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/users/deactivateAll', [
            'id' => [
                '0acad6f2-b47e-4fc1-9086-cbc906dc45fd',
                '968ad2b3-f41d-4de3-909a-74a3ce85e826',
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'afterBulk', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/users');
    }

    /**
     * Test custom finder with options
     *
     * @return void
     */
    public function testPostWithCustomFinder()
    {
        $this->_eventManager->on(
            'Dispatcher.invokeController',
            ['priority' => 1000],
            function ($event) {
                $this->_subscribeToEvents($this->_controller);
                $this->_controller->Crud->action('deactivateAll')
                    ->findMethod(['withCustomOptions' => ['foo' => 'bar']]);
            }
        );

        $this->post('/blogs/deactivateAll', [
            'id' => [
                1,
                2,
            ],
        ]);

        $this->assertSame(['foo' => 'bar'], $this->_controller->Blogs->customOptions);
    }
}
