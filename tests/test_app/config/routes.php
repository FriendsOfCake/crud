<?php
namespace Crud\Test\App\Config;

$routes->scope('/', function ($routes) {
    $routes->setExtensions(['json']);

    $routes->connect('/{controller}', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);
    $routes->connect('/{controller}/{action}/*', [], ['routeClass' => 'InflectedRoute']);
});
