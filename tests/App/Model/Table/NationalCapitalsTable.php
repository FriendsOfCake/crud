<?php
namespace Crud\Test\App\Model\Table;

class NationalCapitalsTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Countries');
    }
}
