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
        'user_id' => ['type' => 'uuid', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    public $records = [
        ['name' => '1st post', 'body' => '1st post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
        ['name' => '2nd post', 'body' => '2nd post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
        ['name' => '3rd post', 'body' => '3rd post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
        ['name' => '4th post', 'body' => '4th post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
        ['name' => '5th post', 'body' => '5th post body', 'user_id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd'],
    ];
}
