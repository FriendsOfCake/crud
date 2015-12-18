<?php
namespace Crud\Action;

use Crud\Event\Subject;

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditAction extends BaseCreateAction
{

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
        'entityCreated' => false,
        'messages' => [
            'success' => [
                'text' => 'Successfully updated {name}'
            ],
            'error' => [
                'text' => 'Could not update {name}'
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
                'url' => ['action' => 'edit', ['subject.key', 'id']]
            ]
        ],
        'api' => [
            'methods' => ['put', 'post'],
            'success' => [
                'code' => 200
            ],
            'error' => [
                'exception' => [
                    'type' => 'validate',
                    'class' => '\Crud\Error\Exception\ValidationException'
                ]
            ]
        ],
        'serialize' => []
    ];

    /**
     * Returns the subject for GET requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return void
     */
    protected function _getSubject(Subject $subject, $id = null)
    {
        $subject->set(['id' => $id]);
        $subject->set(['entity' => $this->_findRecord($id, $subject)]);
        return $subject;
    }

    /**
     * Returns the entity for POST/PUT requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return void
     */
    protected function _postEntity(Subject $subject, $id = null)
    {
        return $this->_table()->patchEntity(
            $this->_findRecord($id, $subject),
            $this->_request()->data,
            $this->saveOptions()
        );
    }
}
