<?php
namespace Crud\Test\App\Model\Table;

class CulturesTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Countries');
    }
}
