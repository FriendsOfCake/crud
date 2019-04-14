<?php

namespace Crud\Test\App\Model\Endpoint;

use Muffin\Webservice\Model\Endpoint;

class SearchEndpoint extends Endpoint
{
    public static function defaultConnectionName()
    {
        return 'test';
    }
}
