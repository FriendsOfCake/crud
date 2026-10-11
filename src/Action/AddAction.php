<?php
declare(strict_types=1);

namespace Crud\Action;

use Cake\Http\Response;
use Crud\Error\Exception\ValidationException;
use Crud\Event\Subject;
use Crud\Traits\RedirectTrait;
use Crud\Traits\SaveMethodTrait;
use Crud\Traits\SerializeTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'Add' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddAction extends BaseAction
{
    use RedirectTrait;
    use SaveMethodTrait;
    use SerializeTrait;
    use ViewTrait;
    use ViewVarTrait;

    /**
     * Default settings for 'add' actions
     *
     * `enabled` Is this crud action enabled or disabled
     *
     * `view` A map of the controller action and the view to render
     * If `NULL` (the default) the controller action name will be used
     *
     * `relatedModels` is a map of the controller action and the whether it should fetch associations lists
     * to be used in select boxes. An array as value means it is enabled and represent the list
     * of model associations to be fetched
     *
     * `saveOptions` Options array used for $options argument of newEntity() and save method.
     * If you configure a key with your action name, it will override the default settings.
     *
     * @var array
     */
    protected array $defaultConfig = [
        'enabled' => true,
        'scope' => 'entity',
        'inflection' => 'singular',
        'saveMethod' => 'save',
        'view' => null,
        'viewVar' => null,
        'relatedModels' => true,
        'entityKey' => 'entity',
        'saveOptions' => [],
        'api' => [
            'methods' => ['put', 'post'],
            'success' => [
                'code' => 201,
                'data' => [
                    'entity' => ['id'],
                ],
            ],
            'error' => [
                'exception' => [
                    'type' => 'validate',
                    'class' => ValidationException::class,
                ],
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
                'url' => ['action' => 'edit', ['entity.field', 'id']],
            ],
        ],
        'messages' => [
            'success' => [
                'text' => 'Successfully created {name}',
            ],
            'error' => [
                'text' => 'Could not create {name}',
            ],
        ],
        'serialize' => [],
    ];

    /**
     * HTTP GET handler
     *
     * @return void
     */
    protected function handleGet(): void
    {
        $subject = $this->subject([
            'success' => true,
            'entity' => $this->entity(
                $this->request()->getQueryParams(),
                ['validate' => false] + $this->saveOptions(),
            ),
        ]);

        $this->trigger('beforeRender', $subject);
    }

    /**
     * HTTP POST handler
     *
     * @return \Cake\Http\Response|null
     */
    protected function handlePost(): ?Response
    {
        $subject = $this->subject([
            'entity' => $this->entity($this->request()->getData(), $this->saveOptions()),
            'saveMethod' => $this->saveMethod(),
            'saveOptions' => $this->saveOptions(),
        ]);

        $event = $this->trigger('beforeSave', $subject);
        if ($event->isStopped()) {
            return $this->stopped($subject);
        }

        $saveCallback = [$this->model(), $subject->saveMethod];
        /** @phpstan-ignore argument.type */
        if (call_user_func($saveCallback, $subject->entity, $subject->saveOptions)) {
            return $this->success($subject);
        }

        $this->error($subject);

        return null;
    }

    /**
     * HTTP PUT handler
     *
     * @return \Cake\Http\Response|null
     */
    protected function handlePut(): ?Response
    {
        return $this->handlePost();
    }

    /**
     * Post success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function success(Subject $subject): ?Response
    {
        $subject->set(['success' => true, 'created' => true]);

        $this->trigger('afterSave', $subject);
        $this->setFlash('success', $subject);

        return $this->redirect($subject, ['action' => 'index']);
    }

    /**
     * Post error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function error(Subject $subject): void
    {
        $subject->set(['success' => false, 'created' => false]);

        $this->trigger('afterSave', $subject);
        $this->setFlash('error', $subject);
        $this->trigger('beforeRender', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function stopped(Subject $subject): ?Response
    {
        if (!isset($subject->success)) {
            $subject->success = false;
        }

        if ($subject->success) {
            return $this->success($subject);
        }

        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->redirect($subject, ['action' => 'index']);
    }
}
