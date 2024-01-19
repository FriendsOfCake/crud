<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

use Cake\Controller\Component\FlashComponent;
use Cake\Core\Configure;
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
    protected $fixtures = ['plugin.Crud.Blogs', 'plugin.Crud.Users'];

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
        $result = (string)$this->_response->getBody();

        $expected = '<legend>Edit Blog</legend>';
        $this->assertStringContainsString($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id" value="1"/>';
        $this->assertStringContainsString($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" id="name" value="1st post" maxlength="255"/>';
        $this->assertStringContainsString($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5">1st post body</textarea>';
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
        $this->get('/blogs/edit/1?name=test');
        $result = (string)$this->_response->getBody();

        $expected = '<legend>Edit Blog</legend>';
        $this->assertStringContainsString($expected, $result, 'legend do not match the expected value');

        $expected = '<input type="hidden" name="id" id="id" value="1"/>';
        $this->assertStringContainsString($expected, $result, '"id" do not match the expected value');

        $expected = '<input type="text" name="name" id="name" value="1st post" maxlength="255"/>';
        $this->assertStringContainsString($expected, $result, '"name" do not match the expected value');

        $expected = '<textarea name="body" id="body" rows="5">1st post body</textarea>';
        $this->assertStringContainsString($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test for custom finder with options.
     *
     * @return void
     */
    public function testCustomFinder()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Crud->action('edit')
                    ->findMethod(['withCustomOptions' => ['foo' => 'bar']]);
            }
        );

        $this->get('/blogs/edit/1');
        $this->assertSame(['foo' => 'bar'], $this->_controller->Blogs->customOptions);
    }

    /**
     * Test POST will update an existing record
     *
     * @return void
     */
    public function testActionPost()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
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
                            'key' => 'flash',
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->post('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
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
                            'key' => 'flash',
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['save'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );
                $blogs
                    ->expects($this->once())
                    ->method('save')
                    ->will($this->returnValue(false));

                $this->getTableLocator()->set('Blogs', $blogs);
            }
        );

        $this->put('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
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
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
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
                            'key' => 'flash',
                        ]
                    );

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

        $this->put('/blogs/edit/1', [
            'name' => 'Hello',
            'body' => 'Pretty hot body',
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

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
     * Test PATCH will update an existing record
     *
     * @return void
     */
    public function testActionPatch()
    {
        $this->_eventManager->on(
            'Controller.initialize',
            ['priority' => 11],
            function ($event) {
                $this->_controller->Flash = $this->getMockBuilder(FlashComponent::class)
                    ->onlyMethods(['set'])
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
                            'key' => 'flash',
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            }
        );

        $this->patch('/blogs/edit/1', [
            'name' => 'Hello World',
            'body' => 'Even hotter body',
        ]);

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertFalse($this->_subject->created);
        $this->assertRedirect('/blogs');
    }
}
