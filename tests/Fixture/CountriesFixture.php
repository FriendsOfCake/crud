<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CountriesFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'integer'],
        'code' => ['type' => 'string', 'length' => 2, 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'currency_id' => ['type' => 'integer', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    public $records = [
        ['id' => 1, 'code' => 'NL', 'name' => 'The Netherlands', 'currency_id' => 1],
        ['id' => 2, 'code' => 'BE', 'name' => 'Belgium', 'currency_id' => 1],
    ];
}
