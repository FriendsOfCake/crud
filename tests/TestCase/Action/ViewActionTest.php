<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ViewActionTest extends IntegrationTestCase
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

        $this->{$method}('/blogs/view/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('blog'));
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
                $this->controller->Crud->action('view')->viewVar('item');
                $this->subscribeToEvents($this->controller);
            },
            ['priority' => 11],
        );

        $this->get('/blogs/view/1');

        $this->assertEvents(['beforeFind', 'afterFind', 'beforeRender']);
        $this->assertNotNull($this->viewVariable('viewVar'));
        $this->assertNotNull($this->viewVariable('item'));
        $this->assertNotNull($this->viewVariable('success'));
    }
}
