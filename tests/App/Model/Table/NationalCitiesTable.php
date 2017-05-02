<?php
namespace Crud\Test\App\Model\Table;

class NationalCitiesTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Countries');
    }
}
