<?php
declare(strict_types=1);

namespace Crud\Test\App\Error;

use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Crud\Error\ExceptionRenderer;
use Throwable;

/**
 * Test double for ExceptionRenderer.
 *
 * PHPUnit is unable to generate a mock for ExceptionRenderer as the class
 * (inherited from Cake's WebExceptionRenderer) has a method named `method()`.
 */
class TestExceptionRenderer extends ExceptionRenderer
{
    /**
     * Controller to be used for rendering.
     *
     * @var \Cake\Controller\Controller|null
     */
    public ?Controller $testController = null;

    /**
     * @param \Throwable $error The exception being rendered.
     * @param \Cake\Http\ServerRequest|null $request The request to use.
     * @param \Cake\Controller\Controller|null $controller Controller to render with.
     */
    public function __construct(Throwable $error, ?ServerRequest $request = null, ?Controller $controller = null)
    {
        $this->testController = $controller;

        parent::__construct($error, $request);
    }

    /**
     * @inheritDoc
     */
    protected function getController(): Controller
    {
        return $this->testController;
    }
}
