<?php
namespace Crud\Action;

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
    protected $_defaultConfig = [
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
                    'entity' => ['id']
                ]
            ],
            'error' => [
                'exception' => [
                    'type' => 'validate',
                    'class' => ValidationException::class
                ]
            ]
        ],
        'redirect' => [
            'post_add' => [
                'reader' => 'request.data',
                'key' => '_add',
                'url' => ['action' => 'add']
            ],
            'post_edit' => [
                'reader' => 'request.data',
                'key' => '_edit',
                'url' => ['action' => 'edit', ['entity.field', 'id']]
            ]
        ],
        'messages' => [
            'success' => [
                'text' => 'Successfully created {name}'
            ],
            'error' => [
                'text' => 'Could not create {name}'
            ]
        ],
        'serialize' => []
    ];

    /**
     * HTTP GET handler
     *
     * @return void
     */
    protected function _get()
    {
        $subject = $this->_subject([
            'success' => true,
            'entity' => $this->_entity($this->_request()->getQuery() ?: null, ['validate' => false] + $this->saveOptions())
        ]);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * HTTP POST handler
     *
     * @return \Cake\Http\Response|null
     */
    protected function _post()
    {
        $subject = $this->_subject([
            'entity' => $this->_entity($this->_request()->getData(), $this->saveOptions()),
            'saveMethod' => $this->saveMethod(),
            'saveOptions' => $this->saveOptions()
        ]);

        $event = $this->_trigger('beforeSave', $subject);
        if ($event->isStopped()) {
            return $this->_stopped($subject);
        }

        $saveCallback = [$this->_table(), $subject->saveMethod];
        if (call_user_func($saveCallback, $subject->entity, $subject->saveOptions)) {
            return $this->_success($subject);
        }

        $this->_error($subject);
    }

    /**
     * HTTP PUT handler
     *
     * @return \Cake\Http\Response|null
     */
    protected function _put()
    {
        return $this->_post();
    }

    /**
     * Post success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response
     */
    protected function _success(Subject $subject)
    {
        $subject->set(['success' => true, 'created' => true]);

        $this->_trigger('afterSave', $subject);
        $this->setFlash('success', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * Post error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject)
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
     * @return \Cake\Http\Response
     */
    protected function _stopped(Subject $subject)
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
