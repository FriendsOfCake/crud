<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class NationalCitiesFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false],
        'country_id' => ['type' => 'integer', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    public $records = [
        ['name' => 'Amsterdam', 'country_id' => 1],
        ['name' => 'Rotterdam', 'country_id' => 1],
        ['name' => 'Brussels', 'country_id' => 2],
        ['name' => 'Antwerp', 'country_id' => 2]
    ];
}
