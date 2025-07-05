<?php
declare(strict_types=1);

namespace Crud\Test\App\Listener;

use Crud\Listener\BaseListener;

class TestListener extends BaseListener
{
    public $callCount = 0;

    protected function setUp()
    {
        $this->callCount += 1;
    }
}
