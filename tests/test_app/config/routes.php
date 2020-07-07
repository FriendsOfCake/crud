<?php
namespace Crud\Test\App\Config;

use Cake\Routing\Router;

Router::scope('/', function ($routes) {
    $routes->extensions(['json']);

    $routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);
    $routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);
});
