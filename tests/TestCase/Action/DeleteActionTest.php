<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

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
    public $fixtures = ['plugin.crud.blogs'];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Data provider with HTTP verbs
     *
     * @return array
     */
    public function allHttpMethodProvider()
    {
        return [
            ['post'],
            ['delete']
        ];
    }

    /**
     * Test the normal HTTP flow for HTTP verbs
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
                        'Successfully deleted blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully deleted blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->once())
                    ->method('delete')
                    ->will($this->returnValue(true));
            }
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
                        'Could not delete blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not delete blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeDelete', function ($event) {
                    $event->stopPropagation();
                });

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->never())
                    ->method('delete');
            }
        );

        $this->post('/blogs/delete/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeDelete', 'setFlash', 'beforeRedirect']);
        $this->assertFalse($this->_subject->success);
        $this->assertRedirect('/blogs');
    }

    /**
     * Test the flow when the beforeRedirect event is stopped (no redirection)
     *
     * @dataProvider allHttpMethodProvider
     * @return void
     */
    public function testStopBeforeRedirect()
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
                        'Successfully deleted blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully deleted blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->on('beforeRedirect', function ($event) {
                    $event->stopPropagation();
                });

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['delete'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->once())
                    ->method('delete')
                    ->will($this->returnValue(true));
            }
        );

        $this->delete('/blogs/delete/2');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeDelete', 'afterDelete', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertNoRedirect();
    }
}
