<?php
namespace Crud\Action\Bulk;

use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Query;
use Crud\Action\BaseAction as CrudBaseAction;
use Cake\Utility\Hash;
use Crud\Event\Subject;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Bulk' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseAction extends CrudBaseAction
{
    use RedirectTrait;

    /**
     * Default settings for 'base' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'all',
        'actionName' => 'Bulk',
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
     * @return \Cake\Network\Response
     */
    protected function _handle()
    {
        $ids = $this->_controller()->request->data('id');
        if (!is_array($ids) || !Hash::numeric(array_keys($ids))) {
            throw new BadRequestException('Bad request data');
        }
        $ids = array_filter($ids);

        $subject = $this->_subject();
        $subject->set(['ids' => $ids]);

        $actionName = $this->config('actionName');

        $this->_trigger(sprintf('before%sFind', $actionName), $subject);
        $query = $this->_table()->find($this->config('findMethod'), $this->_getFindConfig($subject));
        $subject->set(['query' => $query]);

        $this->_trigger(sprintf('after%sFind', $actionName), $subject);

        $event = $this->_trigger(sprintf('before%s', $actionName), $subject);
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
        $actionName = $this->config('actionName');
        $subject->set(['success' => true]);
        $this->_trigger(sprintf('after%s', $actionName), $subject);

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
        $actionName = $this->config('actionName');
        $subject->set(['success' => false]);
        $this->_trigger(sprintf('after%s', $actionName), $subject);

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
     * @param \Cake\ORM\Query $query The query to act upon
     * @return bool
     */
    abstract protected function _bulk(Query $query);
}
