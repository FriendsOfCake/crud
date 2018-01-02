<?php
namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query;

class BlogsTable extends \Cake\ORM\Table
{
    public $customOptions;

    /**
     * findWithCustomOptions
     *
     * @param Query $query
     * @param array $options
     * @return void
     */
    public function findWithCustomOptions(Query $query, array $options)
    {
        $this->customOptions = $options;

        return $query;
    }
}
