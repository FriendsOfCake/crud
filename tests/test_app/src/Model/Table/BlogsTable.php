<?php
declare(strict_types=1);

namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

class BlogsTable extends Table
{
    public array $customOptions;

    /**
     * findWithCustomOptions
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param mixed ...$options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findWithCustomOptions(SelectQuery $query, mixed ...$options)
    {
        $this->customOptions = $options;

        return $query;
    }
}
