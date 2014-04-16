<?php
namespace Crud\Test\App\Controller;

class BlogsController extends \Cake\Controller\Controller {

	use \Crud\Controller\ControllerTrait;

	public $components = [
		'RequestHandler',
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
				'Crud.ApiQueryLog',
				'Crud.RelatedModels'
			]
		]
	];

	public $uses = ['Blog'];

}
