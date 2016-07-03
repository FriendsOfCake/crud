<?php
namespace Crud\Action;

use Crud\Event\Subject;

/**
 * Handles 'Add' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddAction extends BaseCreateAction
{

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
        'entityCreated' => true,
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
                    'class' => '\Crud\Error\Exception\ValidationException'
                ]
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
        'serialize' => []
    ];

    /**
     * Returns the subject for GET requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return \Crud\Event\Subject
     */
    protected function _getSubject(Subject $subject, $id = null)
    {
        $data = $this->_request()->query ?: null;
        $entity = $this->_entity($data, ['validate' => false] + $this->saveOptions());
        $subject->set(['success' => true]);
        $subject->set(['entity' => $entity]);
        return $subject;
    }

    /**
     * Returns the entity for POST/PUT requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return \Cake\ORM\Entity
     */
    protected function _postEntity(Subject $subject, $id = null)
    {
        return $this->_entity($this->_request()->data, $this->saveOptions());
    }
}
