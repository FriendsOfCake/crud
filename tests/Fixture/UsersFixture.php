<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'uuid'],
        'is_active' => ['type' => 'boolean', 'default' => true, 'null' => false],
        'username' => ['type' => 'string', 'length' => 255, 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public $records = [
        ['id' => '0acad6f2-b47e-4fc1-9086-cbc906dc45fd', 'username' => 'test_1'],
        ['id' => '968ad2b3-f41d-4de3-909a-74a3ce85e826', 'username' => 'test_2'],
        ['id' => 'fac4fb37-7d1e-4063-adef-4dcde4c009ef', 'username' => 'test_3'],
        ['id' => 'b2a00e96-c403-48c4-9043-e8fa1f55399b', 'username' => 'test_4'],
        ['id' => 'abd0c4a2-ceea-4f3c-8e00-b82b54982a96', 'username' => 'test_5']
    ];
}
