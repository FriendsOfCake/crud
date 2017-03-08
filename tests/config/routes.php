<?php
namespace Crud\Test\App\Config;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::scope('/', function ($routes) {
    $routes->extensions(['json']);

    $routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);
    $routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);

    $routes->resources('Countries', function (RouteBuilder $routes) {
        $routes->connect(
            '/relationships/:type',
            [
                'controller' => 'Currencies',
                '_method' => 'GET',
                'action' => 'view',
                'from' => 'Countries',
            ],
            [
                'routeClass' => 'Crud.JsonApiRoute',
            ]
        );

        return $routes;
    });
    $routes->resources('Currencies');
    $routes->resources('Cultures');
});
