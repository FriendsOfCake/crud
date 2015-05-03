<?php
namespace Crud\Action;

use Cake\Event\Event;
use Cake\Network\Exception\NotImplementedException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Crud\Error\Exception\ActionNotConfiguredException;
use Crud\Event\Subject;

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
        'findMethod' => 'all',
        'messages' => [
            'success' => [
                'text' => 'Bulk action successfully completed'
            ],
            'error' => [
                'text' => 'Could not complete bulk action'
            ]
        ],
    ];

    /**
     * Handle a bulk event
     *
     * @return void
     */
    protected function _handle()
    {
        $ids = $this->_controller()->request->data('id');
        if (!is_array($data) || !Hash::numeric(array_keys($data))) {
            throw new BadRequestException('Bad request data');
        }
        $ids = array_filter($ids);

        $subject = $this->_subject();
        $subject->set(['ids' => $ids]);

        $this->_trigger('beforeBulkFind', $subject);
        $query = $this->_table()->find($this->config('findMethod'), $this->_getFindConfig($subject));
        $subject->set(['query' => $query]);

        $this->_trigger('afterBulkFind', $subject);

        $event = $this->_trigger('beforeBulk', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        if ($this->_bulk($query)) {
            $this->_success($subject);
        } else {
            $this->_error($subject);
        }

        return $this->_redirect($subject, ['action' => 'index']);
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
     * Success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _success(Subject $subject)
    {
        $subject->set(['success' => true]);
        $this->_trigger('afterBulk', $subject);

        $this->setFlash('success', $subject);
    }

    /**
     * Error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject)
    {
        $subject->set(['success' => false]);
        $this->_trigger('afterBulk', $subject);

        $this->setFlash('error', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Network\Response
     */
    protected function _stopped(Subject $subject)
    {
        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * Handle a bulk event
     *
     * @return void
     */
    abstract protected function _bulk(Query $query);
}
