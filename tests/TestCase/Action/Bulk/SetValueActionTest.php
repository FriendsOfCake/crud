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
class SetValueActionTest extends IntegrationTestCase
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
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Set value successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Set value successfully'],
                            'key' => 'flash',
                        ],
                    );

                $this->controller->components()->set('Flash', $component);

                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->{$method}('/blogs/deactivateAll', [
            'id' => [
                1,
                2,
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'afterBulk', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeBulk event is stopped
     *
     * @return void
     */
    public function testStopBeforeBulk()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not set value',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not set value'],
                            'key' => 'flash',
                        ],
                    );

                $this->controller->components()->set('Flash', $component);

                $this->subscribeToEvents($this->controller);

                $this->controller->Crud->on('beforeBulk', function ($event) {
                    $event->stopPropagation();
                });
            },
            ['priority' => 11],
        );

        $this->post('/blogs/deactivateAll', [
            'id' => [
                1,
                2,
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'setFlash', 'beforeRedirect']);
        $this->assertFalse($this->subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test with UUID request data.
     *
     * @return void
     */
    public function testUuidRequestData()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $component = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
                    ->setConstructorArgs([$this->controller->components()])
                    ->getMock();

                $component
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Set value successfully',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Set value successfully'],
                            'key' => 'flash',
                        ],
                    );

                $this->controller->components()->set('Flash', $component);

                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->post('/users/deactivateAll', [
            'id' => [
                '0acad6f2-b47e-4fc1-9086-cbc906dc45fd',
                '968ad2b3-f41d-4de3-909a-74a3ce85e826',
            ],
        ]);

        $this->assertEvents(['beforeBulk', 'afterBulk', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->subject->success);
        $this->assertRedirect('/users');
    }
}
