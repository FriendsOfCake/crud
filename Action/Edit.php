<?php

namespace Crud\Action;

use Cake\Utility\Hash;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;
use Crud\Traits\SaveMethodTrait;

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Edit extends Base {

	use FindMethodTrait;
	use RedirectTrait;
	use SaveMethodTrait;

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
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'scope' => 'entity',
		'findMethod' => 'all',
		'saveMethod' => 'save',
		'view' => null,
		'relatedModels' => true,
		'saveOptions' => [
			'validate' => true,
			'atomic' => true
		],
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
					'class' => '\Crud\Error\Exception\CrudValidationException'
				]
			]
		],
		'serialize' => []
	];

/**
 * HTTP GET handler
 *
 * @throws NotFoundException If record not found
 * @param string $id
 * @return void
 */
	protected function _get($id = null) {
		$subject = $this->_subject();
		$subject->set(['id' => $id]);

		$this->_request()->data = $this->_findRecord($id, $subject);

		$this->_trigger('beforeRender', $subject);
	}

/**
 * HTTP PUT handler
 *
 * @param mixed $id
 * @return void
 */
	protected function _put($id = null) {
		$subject = $this->_subject();
		$subject->set(['id' => $id]);

		$entity = $this->_findRecord($id, $subject);

		$request = $this->_request();
		$entity->accessible('*', true);
		$entity->set($request->data);
		$request->data = $entity;

		$this->_trigger('beforeSave', $subject);
		if (call_user_func([$this->_repository(), $this->saveMethod()], $entity, $this->saveOptions())) {
			return $this->_success($subject);
		}

		return $this->_error($subject);
	}

/**
 * HTTP POST handler
 *
 * Thin proxy for _put
 *
 * @param mixed $id
 * @return void
 */
	protected function _post($id = null) {
		return $this->_put($id);
	}

/**
 * Success callback
 *
 * @param  Subject $subject
 * @return \Cake\Network\Response
 */
	protected function _success(Subject $subject) {
		$subject->set(['success' => true, 'created' => false]);
		$this->_trigger('afterSave', $subject);

		$this->setFlash('success', $subject);

		return $this->_redirect($subject, ['action' => 'index']);
	}

/**
 * Error callback
 *
 * @param  Subject $subject
 * @return void
 */
	protected function _error(Subject $subject) {
		$subject->set(['success' => false, 'created' => false]);
		$this->_trigger('afterSave', $subject);

		$this->setFlash('error', $subject);

		$this->_trigger('beforeRender', $subject);
	}

}
