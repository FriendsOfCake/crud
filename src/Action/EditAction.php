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
    protected $_defaultConfig = [
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
     * @param string|null $id Record id
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If record not found
     */
    protected function _get(?string $id = null): void
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);
        $subject->set(['entity' => $this->_findRecord($id, $subject)]);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * HTTP PUT handler
     *
     * @param string|null $id Record id
     * @return \Cake\Http\Response|void
     */
    protected function _put(?string $id = null)
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);

        $entity = $this->_table()->patchEntity(
            $this->_findRecord($id, $subject),
            $this->_request()->getData(),
            $this->saveOptions()
        );

        $this->_trigger('beforeSave', $subject);
        if (call_user_func([$this->_table(), $this->saveMethod()], $entity, $this->saveOptions())) {
            return $this->_success($subject);
        }

        $this->_error($subject);
    }

    /**
     * HTTP POST handler
     *
     * Thin proxy for _put
     *
     * @param string|null $id Record id
     * @return \Cake\Http\Response|void
     */
    protected function _post(?string $id = null)
    {
        return $this->_put($id);
    }

    /**
     * HTTP PATCH handler
     *
     * Thin proxy for _put
     *
     * @param string|null $id Record id
     * @return \Cake\Http\Response|void
     */
    protected function _patch(?string $id = null)
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
}
