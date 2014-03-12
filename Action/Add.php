<?php
namespace Crud\Action;

use Crud\Event\Subject;

/**
 * Handles 'Add' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Add extends Base {

	use \Crud\Traits\RedirectTrait;
	use \Crud\Traits\SaveMethodTrait;
	use \Crud\Traits\SerializeTrait;

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
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'scope' => 'repository',
		'saveMethod' => 'save',
		'view' => null,
		'relatedModels' => true,
		'saveOptions' => [
			'validate' => true,
			'atomic' => true
		],
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
					'class' => '\Crud\Error\Exception\CrudValidationException'
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
				'url' => ['action' => 'edit', ['subject.key', 'id']]
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
	protected function _get() {
		$this->_request()->data = $this->_entity();
		$this->_trigger('beforeRender', ['success' => true]);
	}

/**
 * HTTP POST handler
 *
 * @return void
 */
	protected function _post() {
		$entity = $this->_getEntity();
		$subject = $this->_subject();
		$subject->set([
			'item' => $entity,
			'saveMethod' => $this->saveMethod(),
			'saveOptions' => $this->saveOptions()
		]);

		$this->_trigger('beforeSave', $subject);
		if (call_user_func([$this->_repository(), $subject->saveMethod], $entity, $subject->saveOptions)) {
			return $this->_success($subject);
		}

		return $this->_error($subject);
	}

/**
 * HTTP PUT handler
 *
 * @return void
 */
	protected function _put() {
		return $this->_post();
	}

/**
 * Post success callback
 *
 * @param  Subject $subject
 * @return \Cake\Network\Response
 */
	protected function _success(Subject $subject) {
		$subject->set(['success' => true, 'created' => true]);

		$this->_trigger('afterSave', $subject);
		$this->setFlash('success', $subject);

		$this->_redirect($subject, ['action' => 'index']);
	}

/**
 * Post error callback
 *
 * @param  Subject $subject
 * @return void
 */
	protected function _error(Subject $subject) {
		$subject->set(['success' => false, 'created' => false]);

		$this->setFlash('error', $subject);

		$this->_trigger('afterSave', $subject);
		$this->_trigger('beforeRender', $subject);
	}

/**
 * Get entity instance with POST data
 *
 * @return \Cake\ORM\Entity
 */
	protected function _getEntity() {
		$entity = $this->_entity();
		$entity->accessible('*', true);
		$entity->set($this->_request()->data);

		return $entity;
	}

}
