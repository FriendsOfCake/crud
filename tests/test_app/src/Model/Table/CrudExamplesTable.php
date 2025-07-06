<?php
declare(strict_types=1);

namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/**
 * Crud Example Model
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class CrudExamplesTable extends Table
{
    protected ?string $_alias = 'CrudExamples';

    /**
     * @param array $config Config
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('posts');
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findPublished(SelectQuery $query, array $options): void
    {
        $query->where(['published' => 'Y']);
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findUnpublished(SelectQuery $query, array $options): void
    {
        $query->where(['published' => 'N']);
    }
}
