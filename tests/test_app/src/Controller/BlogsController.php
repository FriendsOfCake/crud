<?php
declare(strict_types=1);

namespace Crud\Test\App\Controller;

use Cake\Controller\Controller;
use Crud\Controller\ControllerTrait;

class BlogsController extends Controller
{
    use ControllerTrait;

    public $paginate = ['limit' => 3];

    public function initialize(): void
    {
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
                'Crud.Lookup',
                'deleteAll' => [
                    'className' => 'Crud.Bulk/Delete',
                ],
                'toggleActiveAll' => [
                    'className' => 'Crud.Bulk/Toggle',
                    'field' => 'is_active',
                ],
                'deactivateAll' => [
                    'className' => 'Crud.Bulk/SetValue',
                    'field' => 'is_active',
                    'value' => false,
                ],
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.RelatedModels',
                'Crud.Redirect',
            ],
        ]);
    }
}
