<?php
namespace Crud\Test\App\Controller;

use Cake\Controller\Controller;
use Crud\Controller\ControllerTrait;

class BlogsController extends Controller {

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
				'Crud.Delete'
			],
			'listeners' => [
				'Crud.Api',
				'Crud.RelatedModels'
			]
		]
	];

}
