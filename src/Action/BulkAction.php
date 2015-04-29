<?php
namespace Crud\Action;

use Cake\Network\Exception\NotImplementedException;
use Cake\Event\Event;
use Cake\ORM\Query;
use Crud\Event\Subject;
use Crud\Error\Exception\ActionNotConfiguredException;

/**
 * Handles 'Bulk' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BulkAction extends BaseAction
{
    /**
     * Default settings for 'lookup' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => false,
        'scope' => 'table',
        'findMethod' => 'all'
    ];

    /**
     * Handle a bulk event
     *
     * @return void
     */
    protected function _handle()
    {
        $ids = $this->_controller()->request->data('id');
        $ids = array_filter($ids);

        $subject = $this->_subject();
        $subject->set(['ids' => $ids]);

        $this->_trigger('beforeBulkFind', $subject);
        $query = $this->_table()->find($this->config('findMethod'), $this->_getFindConfig($subject));
        $subject->set(['query' => $query]);

        $this->_trigger('afterBulkFind', $subject);

        $this->_bulk($query);
    }

    /**
     * Get the query configuration
     *
     * @return array
     */
    protected function _getFindConfig(Subject $subject)
    {
        $config = (array)$this->config('findConfig');
        if (!empty($config)) {
            return $config;
        }

        $ids = $subject->ids;
        $primaryKey = $this->_table()->primaryKey();
        $config['conditions'][] = function ($exp) use ($primaryKey, $ids) {
            return $exp->in($primaryKey, $ids);
        };

        return $config;
    }

    /**
     * Handle a bulk event
     *
     * @return void
     */
    abstract protected function _bulk(Query $query);
}
