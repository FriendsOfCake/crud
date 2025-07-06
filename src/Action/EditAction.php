<?php
declare(strict_types=1);

namespace Crud\Action;

use Cake\Http\Response;
use Crud\Error\Exception\ValidationException;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;
use Crud\Traits\SaveMethodTrait;
use Crud\Traits\SerializeTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditAction extends BaseAction
{
    use FindMethodTrait;
    use RedirectTrait;
    use SaveMethodTrait;
    use SerializeTrait;
    use ViewTrait;
    use ViewVarTrait;

    /**
     * Default settings for 'edit' actions
     *
     * `enabled` Is this crud action enabled or disabled
     *
     * `findMethod` The default `Model::find()` method for reading data
     *
     * `view` A map of the controller action and the view to render
     * If `NULL` (the default) the controller action name will be used
     *
     * `relatedModels` is a map of the controller action and the whether it should fetch associations lists
     * to be used in select boxes. An array as value means it is enabled and represent the list
     * of model associations to be fetched
     *
     * `saveOptions` Options array used for $options argument of patchEntity() and save method.
     * If you configure a key with your action name, it will override the default settings.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'enabled' => true,
        'scope' => 'entity',
        'findMethod' => 'all',
        'saveMethod' => 'save',
        'view' => null,
        'relatedModels' => true,
        'saveOptions' => [],
        'messages' => [
            'success' => [
                'text' => 'Successfully updated {name}',
            ],
            'error' => [
                'text' => 'Could not update {name}',
            ],
        ],
        'redirect' => [
            'post_add' => [
                'reader' => 'request.data',
                'key' => '_add',
                'url' => ['action' => 'add'],
            ],
            'post_edit' => [
                'reader' => 'request.data',
                'key' => '_edit',
                'url' => ['action' => 'edit', ['subject.key', 'id']],
            ],
        ],
        'api' => [
            'methods' => ['put', 'post', 'patch'],
            'success' => [
                'code' => 200,
            ],
            'error' => [
                'exception' => [
                    'type' => 'validate',
                    'class' => ValidationException::class,
                ],
            ],
        ],
        'serialize' => [],
    ];

    /**
     * HTTP GET handler
     *
     * @param string|int|null $id Record id
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If record not found
     */
    protected function _get(string|int|null $id = null): void
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);
        $subject->set(['entity' => $this->_findRecord($id, $subject)]);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * HTTP PUT handler
     *
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _put(string|int|null $id = null): ?Response
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_model()->patchEntity(
            $this->_findRecord($id, $subject),
            $this->_request()->getData(),
            $this->saveOptions(),
        );

        $subject->set([
            'entity' => $entity,
            'saveMethod' => $this->saveMethod(),
            'saveOptions' => $this->saveOptions(),
        ]);

        $event = $this->_trigger('beforeSave', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        /** @phpstan-ignore argument.type */
        if (call_user_func([$this->_model(), $subject->saveMethod], $subject->entity, $subject->saveOptions)) {
            return $this->_success($subject);
        }

        $this->_error($subject);

        return null;
    }

    /**
     * HTTP POST handler
     *
     * Thin proxy for _put
     *
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _post(string|int|null $id = null): ?Response
    {
        return $this->_put($id);
    }

    /**
     * HTTP PATCH handler
     *
     * Thin proxy for _put
     *
     * @param string|int|null $id Record id
     * @return \Cake\Http\Response|null
     */
    protected function _patch(string|int|null $id = null): ?Response
    {
        return $this->_put($id);
    }

    /**
     * Success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function _success(Subject $subject): ?Response
    {
        $subject->set(['success' => true, 'created' => false]);
        $this->_trigger('afterSave', $subject);

        $this->setFlash('success', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * Error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject): void
    {
        $subject->set(['success' => false, 'created' => false]);
        $this->_trigger('afterSave', $subject);

        $this->setFlash('error', $subject);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function _stopped(Subject $subject): ?Response
    {
        if (!isset($subject->success)) {
            $subject->success = false;
        }

        if ($subject->success) {
            return $this->_success($subject);
        }

        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }
}
