<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BlogFixture extends TestFixture {

	public $fields = [
		'id' => ['type' => 'integer'],
		'name' => ['type' => 'string', 'length' => 255,  'null' => false],
		'body' => ['type' => 'text', 'null' => false],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']]
		]
	];

/**
 * records property
 *
 * @var array
 */
	public $records = [
		['id' => 1, 'name' => '1st post', 'body' => '1st post body'],
		['id' => 2, 'name' => '2nd post', 'body' => '2nd post body'],
		['id' => 3, 'name' => '3rd post', 'body' => '3rd post body'],
		['id' => 4, 'name' => '4th post', 'body' => '4th post body'],
		['id' => 5, 'name' => '5th post', 'body' => '5th post body']
	];

}
