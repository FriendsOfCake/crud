<?php
declare(strict_types=1);

namespace Crud\Action;

use Cake\Http\Response;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;
use Crud\Traits\SerializeTrait;

/**
 * Handles 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteAction extends BaseAction
{
    use FindMethodTrait;
    use RedirectTrait;
    use SerializeTrait;

    /**
     * Default settings for 'add' actions
     *
     * `enabled` Is this crud action enabled or disabled
     *
     * `findMethod` The default `Model::find()` method for reading data
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'entity',
        'findMethod' => 'all',
        'deleteMethod' => 'delete',
        'messages' => [
            'success' => [
                'text' => 'Successfully deleted {name}',
            ],
            'error' => [
                'text' => 'Could not delete {name}',
            ],
        ],
        'api' => [
            'success' => [
                'code' => 200,
            ],
            'error' => [
                'code' => 400,
            ],
        ],
    ];

    /**
     * HTTP POST handler
     *
     * @param string|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _post(?string $id = null): ?Response
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_findRecord($id, $subject);

        $event = $this->_trigger('beforeDelete', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        $method = $this->getConfig('deleteMethod');
        if ($this->_table()->$method($entity)) {
            $this->_success($subject);
        } else {
            $this->_error($subject);
        }

        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * HTTP DELETE handler
     *
     * @param string|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _delete(?string $id = null): ?Response
    {
        return $this->_post($id);
    }

    /**
     * Success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _success(Subject $subject): void
    {
        $subject->set(['success' => true]);
        $this->_trigger('afterDelete', $subject);

        $this->setFlash('success', $subject);
    }

    /**
     * Error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject): void
    {
        $subject->set(['success' => false]);
        $this->_trigger('afterDelete', $subject);

        $this->setFlash('error', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function _stopped(Subject $subject): ?Response
    {
        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }
}
