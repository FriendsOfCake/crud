<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action\Bulk;

use Cake\Controller\Component\FlashComponent;
use Crud\TestSuite\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteActionTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'plugin.Crud.Blogs',
        'plugin.Crud.Users',
    ];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public string $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Data provider with all HTTP verbs
     *
     * @return array
     */
    public static function allHttpMethodProvider()
    {
        return [
            ['post'],
            ['put'],
        ];
    }

    /**
     * Test the normal HTTP flow for all HTTP verbs
     *
     * @return void
     */
    #[DataProvider('allHttpMethodProvider')]
    public function testAllRequestMethods($method)
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
                        'Delete completed successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Delete completed successfully'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->{$method}('/blogs/deleteAll', [
            'id' => [
                1 => 1,
                2 => 2,
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
                        'Could not complete deletion',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not complete deletion'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeBulk', function ($event) {
                    $event->stopPropagation();
                });
            }
        );

        $this->post('/blogs/deleteAll', [
            'id' => [
                1 => 1,
                2 => 2,
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
                        'Delete completed successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Delete completed successfully'],
                            'key' => 'flash',
                        ]
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/users/deleteAll', [
            'id' => [
                '0acad6f2-b47e-4fc1-9086-cbc906dc45fd' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd',
                '968ad2b3-f41d-4de3-909a-74a3ce85e826' => '968ad2b3-f41d-4de3-909a-74a3ce85e826',
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'afterBulk', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/users');
    }
}
