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
     * [initialize description]
     *
     * @param array $config Config
     * @return void
     */
    public function initialize(array $config): void
    {
        $this->setTable('posts');
    }

    /**
     * [_findPublished description]
     *
     * @param \Cake\ORM\Query\SelectQuery $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findPublished(SelectQuery $query, array $options)
    {
        $query->where(['published' => 'Y']);
    }

    /**
     * [_findUnpublished description]
     *
     * @param \Cake\ORM\Query\SelectQuery $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findUnpublished(SelectQuery $query, array $options)
    {
        $query->where(['published' => 'N']);
    }
}
