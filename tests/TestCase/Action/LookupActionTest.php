<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\IntegrationTestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class LookupActionTest extends IntegrationTestCase
{
    /**
     * fixtures property
     *
     * @var array
     */
    protected array $fixtures = ['plugin.Crud.Blogs'];

    /**
     * Test with no extra options
     *
     * @return void
     */
    public function testGet()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $expected = [
            '1' => '1st post',
            '2' => '2nd post',
            '3' => '3rd post',
        ];

        $this->get('/blogs/lookup.json');
        $this->assertEvents(['beforeLookup', 'afterLookup', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blogs'));
        $this->assertNotNull($this->viewVariable('success'));
        $this->assertEquals($expected, $this->viewVariable('blogs')->toArray());
    }

    /**
     * Test changing the id field and value field
     *
     * @return void
     */
    public function testGetWithCustomParams()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $expected = [
            '1st post' => '1',
            '2nd post' => '2',
            '3rd post' => '3',
        ];

        $this->get('/blogs/lookup.json?id=name&value=id');
        $this->assertEvents(['beforeLookup', 'afterLookup', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blogs'));
        $this->assertNotNull($this->viewVariable('success'));
        $this->assertEquals($expected, $this->viewVariable('blogs')->toArray());
    }

    /**
     * Test changing the key field and value field
     *
     * @return void
     */
    public function testGetWithCustomKeyValueFields()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $expected = [
            '1st post' => '1',
            '2nd post' => '2',
            '3rd post' => '3',
        ];

        $this->get('/blogs/lookup.json?key_field=name&value_field=id');
        $this->assertEvents(['beforeLookup', 'afterLookup', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blogs'));
        $this->assertNotNull($this->viewVariable('success'));
        $this->assertEquals($expected, $this->viewVariable('blogs')->toArray());
    }

    /**
     * Tests that the beforeLookup can be used to modify the query
     *
     * @return void
     */
    public function testGetWithQueryModification()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->controller->Crud->on('beforeLookup', function ($event) {
                    $event->getSubject()->query->where(['id <' => 2]);
                });
            },
            ['priority' => 11],
        );

        $expected = [
            '1' => '1st post',
        ];

        $this->get('/blogs/lookup.json');
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertEquals($expected, $this->viewVariable('blogs')->toArray());
    }

    /**
     * Test using custom finder with options.
     *
     * @return void
     */
    public function testGetWithCustomFinder()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->subscribeToEvents($this->controller);
                $this->controller->Crud->action('lookup')
                    ->findMethod(['withCustomOptions' => ['foo' => 'bar']]);
            },
            ['priority' => 11],
        );

        $this->get('/blogs/lookup.json');
        $this->assertSame(['foo' => 'bar'], $this->controller->Blogs->customOptions);
    }
}
