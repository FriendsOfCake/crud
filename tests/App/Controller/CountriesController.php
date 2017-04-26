<?php
namespace Crud\Test\App\Controller;

use Cake\Controller\Controller;
use Crud\Controller\ControllerTrait;

/**
 * Test controller for JsonApiListener integration tests.
 */
class CountriesController extends Controller
{

    use ControllerTrait;

    public $paginate = ['limit' => 3];

    public $components = [
        'RequestHandler',
        'Flash',
        'Crud.Crud' => [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.JsonApi',
                'Crud.ApiPagination',
            ]
        ]
    ];
}
