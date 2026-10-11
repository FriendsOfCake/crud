<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexActionTest extends IntegrationTestCase
{
    /**
     * fixtures property
     *
     * @var array
     */
    protected array $fixtures = ['plugin.Crud.Blogs'];

    /**
     * Data provider with all HTTP verbs
     *
     * @return array
     */
    public static function allHttpMethodProvider()
    {
        return [
            ['get'],
            ['post'],
            ['put'],
            ['delete'],
        ];
    }

    /**
     * Test the normal HTTP flow for all HTTP verbs
     *
     * @return void
     */
    #[DataProvider('allHttpMethodProvider')]
    public function testGet($method)
    {
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->{$method}('/blogs');
        $this->assertStringContainsString(
            'Page 1 of 2, showing 3 records out of 5 total',
            (string)$this->response->getBody(),
        );
        $this->assertEvents(['beforePaginate', 'afterPaginate', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blogs'));
        $this->assertNotNull($this->viewVariable('success'));
    }

    /**
     * Test that changing the viewVar reflects in controller::$viewVar
     *
     * @return void
     */
    public function testGetWithViewVar()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $this->controller->Crud->action('index')->viewVar('items');
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->get('/blogs');

        $this->assertStringContainsString(
            'Page 1 of 2, showing 3 records out of 5 total',
            (string)$this->response->getBody(),
        );
        $this->assertEvents(['beforePaginate', 'afterPaginate', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('items'));
        $this->assertNotNull($this->viewVariable('success'));
    }

    /**
     * Tests that it is posisble to modify the pagination query in beforePaginate
     *
     * @return void
     */
    public function testModifyQueryInEvent()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function () {
                $this->controller->Crud->on('beforePaginate', function ($event) {
                    $event->getSubject()->query->where(['id <' => 2]);
                });
            },
            ['priority' => 11],
        );

        $this->get('/blogs');
        $this->assertStringContainsString(
            'Page 1 of 1, showing 1 records out of 1 total',
            (string)$this->response->getBody(),
        );
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
                $this->controller->Crud->action('index')
                    ->findMethod(['withCustomOptions' => ['foo' => 'bar']]);
            },
            ['priority' => 11],
        );

        $this->get('/blogs');
        $this->assertSame(['foo' => 'bar'], $this->controller->Blogs->customOptions);
    }

    /**
     * Test that trying to access a non existent page number redirects to 1st page.
     *
     * @return void
     */
    public function testForPageOutOfBounds()
    {
        $this->eventManager->on(
            'Controller.initialize',
            function ($event) {
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->get('/blogs?page=999&foo=bar');
        $this->assertSame(302, $this->response->getStatusCode());
        $this->assertSame('http://localhost/blogs?page=2&foo=bar', $this->response->getHeaderLine('Location'));
    }
}
