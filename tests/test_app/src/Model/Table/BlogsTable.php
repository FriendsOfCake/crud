<?php
declare(strict_types=1);

namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;

class BlogsTable extends Table
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
