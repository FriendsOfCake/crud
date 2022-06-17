<?php
declare(strict_types=1);

namespace Crud\Test\App\Controller;

use Cake\Controller\Controller;
use Crud\Controller\ControllerTrait;

class CrudExamplesController extends Controller
{
    use ControllerTrait;

    public ?string $defaultTable = 'CrudExamples';

    public static $componentsArray = [
        'Crud.Crud' => [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete',
                'Crud.View',
            ],
        ],
    ];

    public array $paginate = [
        'limit' => 1000,
    ];

    /**
     * Make it possible to dynamically define the components array during tests
     *
     * @return void
     */
    public function initialize(): void
    {
        foreach (self::$componentsArray as $plugin => $config) {
            $this->loadComponent($plugin, $config);
        }
    }

    /**
     * add
     *
     * Used in the testAddActionTranslatedBaseline test
     *
     * @return void
     */
    public function add()
    {
        return $this->Crud->execute();
    }

    /**
     * Test that it should render 'search.ctp'
     *
     * @return void
     */
    public function search()
    {
        return $this->Crud->execute('index');
    }

    /**
     * Test that it should render 'index'
     *
     * @return void
     */
    public function index()
    {
        return $this->Crud->execute('index');
    }
}
