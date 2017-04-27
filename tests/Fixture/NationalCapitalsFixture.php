<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class NationalCapitalsFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
        'description' => ['type' => 'string', 'length' => 255, 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    public $records = [
        ['name' => 'Amsterdam', 'description' => 'National capital of the Netherlands'],
        ['name' => 'Brussels', 'description' => 'National capital of Belgium'],
        ['name' => 'Wellington', 'description' => 'National capital of New Zealand'],
    ];
}
