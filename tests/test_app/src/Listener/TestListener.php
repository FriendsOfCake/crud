<?php
declare(strict_types=1);
namespace Crud\Test\App\Listener;

class TestListener extends \Crud\Listener\BaseListener
{
    public $callCount = 0;

    public function setup()
    {
        $this->callCount += 1;
    }
}
