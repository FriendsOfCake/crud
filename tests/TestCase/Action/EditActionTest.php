<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditActionTest extends IntegrationTestCase
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
        $this->get('/blogs/edit/1');
        $result = $this->_response->body();

        $expected = '<legend>Edit Blog</legend>';
        $this->assertContains($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id" value="1"/>';
        $this->assertContains($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" maxlength="255" id="name" value="1st post"/>';
        $this->assertContains($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5">1st post body</textarea>';
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
        $this->get('/blogs/edit/1?name=test');
        $result = $this->_response->body();

        $expected = '<legend>Edit Blog</legend>';
        $this->assertContains($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id" value="1"/>';
        $this->assertContains($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" maxlength="255" id="name" value="1st post"/>';
        $this->assertContains($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5">1st post body</textarea>';
        $this->assertContains($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test POST will update an existing record
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
                        'Successfully updated blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully updated blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertFalse($this->_subject->created);
        $this->assertRedirect('/blogs');
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
                        'Could not update blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not update blog'],
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

        $this->put('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRender']);
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
                        'Could not update blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not update blog'],
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

        $this->put('/blogs/edit/1', [
            'name' => 'Hello',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);

        $expected = '<div class="error-message">Name need to be at least 10 characters long</div>';
        $this->assertContains($expected, $this->_response->body(), 'Could not find validation error in HTML');
    }

    /**
     * Test PATCH will update an existing record
     *
     * @return void
     */
    public function testActionPatch()
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
                        'Successfully updated blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully updated blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->patch('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Even hotter body'
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertFalse($this->_subject->created);
        $this->assertRedirect('/blogs');
    }
}
