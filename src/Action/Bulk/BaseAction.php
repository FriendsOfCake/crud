<?php
namespace Crud\Action\Bulk;

use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Crud\Action\BaseAction as CrudBaseAction;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Bulk' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseAction extends CrudBaseAction
{
    use FindMethodTrait;
    use RedirectTrait;

    /**
     * Default settings for 'base' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'bulk',
        'findMethod' => 'all',
        'findConfig' => [],
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
     * @return \Cake\Http\Response
     */
    protected function _handle()
    {
        $ids = $this->_processIds();
        $subject = $this->_constructSubject($ids);

        $event = $this->_trigger('beforeBulk', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        if ($this->_bulk($subject->query)) {
            $this->_success($subject);
        } else {
            $this->_error($subject);
        }

        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * Retrieves a list of ids to process
     *
     * @return array
     */
    protected function _processIds()
    {
        $ids = $this->_controller()->request->getData('id');

        $all = false;
        if (is_array($ids)) {
            $all = Hash::get($ids, '_all', false);
            unset($ids['_all']);
        }

        if (!is_array($ids)) {
            throw new BadRequestException('Bad request data');
        }

        if ($all) {
            foreach ($ids as $key => $value) {
                $ids[$key] = 1;
            }
            $ids = array_keys($ids);
        }

        $ids = array_filter($ids);

        return array_values($ids);
    }

    /**
     * Setup a query object for retrieving entities
     *
     * @param array $ids An array of ids to retrieve
     * @return \Crud\Event\Subject
     */
    protected function _constructSubject(array $ids)
    {
        $repository = $this->_table();

        list($finder, $options) = $this->_extractFinder();
        $options = array_merge($options, $this->_getFindConfig($ids));
        $query = $repository->find($finder, $options);

        $subject = $this->_subject();
        $subject->set([
            'ids' => $ids,
            'query' => $query,
            'repository' => $repository,
        ]);

        return $subject;
    }

    /**
     * Get the query configuration
     *
     * @param array $ids An array of ids to retrieve
     * @return array
     */
    protected function _getFindConfig(array $ids)
    {
        $config = (array)$this->getConfig('findConfig');
        if (!empty($config)) {
            return $config;
        }

        $primaryKey = $this->_table()->primaryKey();
        $config['conditions'] = [];
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
     * @return \Cake\Http\Response
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
     * @param \Cake\ORM\Query|null $query The query to act upon
     * @return bool
     */
    abstract protected function _bulk(Query $query = null);
}
