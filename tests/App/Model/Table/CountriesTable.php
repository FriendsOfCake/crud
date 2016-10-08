<?php
namespace Crud\Test\App\Model\Table;

class CountriesTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Currencies');

        $this->hasMany('Cultures');
    }
}
