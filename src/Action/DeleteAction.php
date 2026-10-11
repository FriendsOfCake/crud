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
    protected array $defaultConfig = [
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
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function handlePost(string|int|null $id = null): ?Response
    {
        $subject = $this->subject();
        $subject->set(['id' => $id]);

        $entity = $this->findRecord($id, $subject);

        $event = $this->trigger('beforeDelete', $subject);
        if ($event->isStopped()) {
            return $this->stopped($subject);
        }

        $method = $this->getConfig('deleteMethod');
        if ($this->model()->$method($entity)) {
            $this->success($subject);
        } else {
            $this->error($subject);
        }

        return $this->redirect($subject, ['action' => 'index']);
    }

    /**
     * HTTP DELETE handler
     *
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function handleDelete(string|int|null $id = null): ?Response
    {
        return $this->handlePost($id);
    }

    /**
     * Success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function success(Subject $subject): void
    {
        $subject->set(['success' => true]);
        $this->trigger('afterDelete', $subject);

        $this->setFlash('success', $subject);
    }

    /**
     * Error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function error(Subject $subject): void
    {
        $subject->set(['success' => false]);
        $this->trigger('afterDelete', $subject);

        $this->setFlash('error', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function stopped(Subject $subject): ?Response
    {
        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->redirect($subject, ['action' => 'index']);
    }
}
