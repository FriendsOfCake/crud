<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CurrenciesFixture extends TestFixture
{

    public $fields = [
        'id' => ['type' => 'integer'],
        'code' => ['type' => 'string', 'length' => 3, 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    public $records = [
        ['id' => 1, 'code' => 'EUR', 'name' => 'Euro'],
        ['id' => 2, 'code' => 'USD', 'name' => 'US Dollar'],
    ];
}
