<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BlogsFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'integer'],
        'is_active' => ['type' => 'boolean', 'default' => true, 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'body' => ['type' => 'text', 'null' => false],
        'user_id' => ['type' => 'uuid', 'default' => null, 'null' => true],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public $records = [
        ['name' => '1st post', 'body' => '1st post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
        ['name' => '2nd post', 'body' => '2nd post body', 'user_id' => '968ad2b3-f41d-4de3-909a-74a3ce85e826'],
        ['name' => '3rd post', 'body' => '3rd post body'],
        ['name' => '4th post', 'body' => '4th post body'],
        ['name' => '5th post', 'body' => '5th post body']
    ];
}
