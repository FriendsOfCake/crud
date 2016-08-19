<?php
namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query;

/**
 * Crud Example Model
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class CrudExamplesTable extends \Cake\ORM\Table
{

    public $alias = 'CrudExamples';

    public $findMethods = [
        'published' => true,
        'unpublished' => true,
        'firstPublished' => true,
        'firstUnpublished' => true,
    ];

    /**
     * [initialize description]
     *
     * @param array $config Config
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('posts');
    }

    /**
     * [_findPublished description]
     *
     * @param Query $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findPublished(Query $query, array $options)
    {
        $query->where(['published' => 'Y']);

        return $query;
    }

    /**
     * [_findUnpublished description]
     *
     * @param Query $query Config
     * @param array $options Options
     * @return void
     */
    protected function _findUnpublished(Query $query, array $options)
    {
        $query->where(['published' => 'N']);

        return $query;
    }
}
