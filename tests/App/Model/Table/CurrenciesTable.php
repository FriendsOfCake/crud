<?php
namespace Crud\Test\App\Model\Table;

class CurrenciesTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->hasMany('Countries');
    }
}
