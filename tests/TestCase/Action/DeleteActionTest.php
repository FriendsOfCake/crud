<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

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
     * Data provider with HTTP verbs
     *
     * @return array
     */
    public static function allHttpMethodProvider()
    {
        return [
            ['post'],
            ['delete'],
        ];
    }

    /**
     * Test the normal HTTP flow for HTTP verbs
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
                        'Successfully deleted blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully deleted blog'],
                            'key' => 'flash',
                        ],
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs'],
                );
                $blogs
                    ->expects($this->once())
                    ->method('delete')
                    ->willReturn(true);

                $this->getTableLocator()->set('Blogs', $blogs);
            },
        );

        $this->{$method}('/blogs/delete/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeDelete', 'afterDelete', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeDelete event is stopped
     *
     * @return void
     */
    public function testStopDelete()
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
                        'Could not delete blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not delete blog'],
                            'key' => 'flash',
                        ],
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeDelete', function ($event) {
                    $event->stopPropagation();
                });

                $blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs'],
                );
                $blogs
                    ->expects($this->never())
                    ->method('delete');

                $this->getTableLocator()->set('Blogs', $blogs);
            },
        );

        $this->post('/blogs/delete/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeDelete', 'setFlash', 'beforeRedirect']);
        $this->assertFalse($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeRedirect event is stopped (no redirection)
     *
     * @return void
     */
    public function testStopBeforeRedirect()
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
                        'Successfully deleted blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully deleted blog'],
                            'key' => 'flash',
                        ],
                    );

                $this->_controller->components()->set('Flash', $component);

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeRedirect', function ($event) {
                    $event->stopPropagation();
                });

                $blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs'],
                );
                $blogs
                    ->expects($this->once())
                    ->method('delete')
                    ->willReturn(true);

                $this->getTableLocator()->set('Blogs', $blogs);
            },
        );

        $this->delete('/blogs/delete/2');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeDelete', 'afterDelete', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertNoRedirect();
    }
}
