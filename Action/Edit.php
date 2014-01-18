<?php

namespace Crud\Action;

use Cake\Utility\Hash;
use Crud\Event\Subject;
use Crud\Traits\SaveMethodTrait;
use Crud\Traits\SaveOptionsTrait;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Edit extends Base {

	use SaveMethodTrait;
	use SaveOptionsTrait;
	use RedirectTrait;

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
 * `validateId` ID Argument validation - by default it will inspect your model's primary key
 * and based on its data type either use integer or UUID validation.
 * Can be disabled by setting it to "false". Supports "integer" and "uuid" configuration
 * By default its configuration is NULL, which means "auto detect"
 *
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'findMethod' => 'all',
		'saveMethod' => 'save',
		'view' => null,
		'relatedModels' => true,
		'validateId' => null,
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
		if (!$this->_validateId($id)) {
			return false;
		}

		$request = $this->_request();
		$request->data = $this->_findRecord($id);
		if (empty($request->data)) {
			return $this->_notFound($id);
		}

		$item = $request->data;
		$subject = $this->_trigger('afterFind', compact('id', 'item'));

		$this->_trigger('beforeRender');
	}

/**
 * HTTP PUT handler
 *
 * @param mixed $id
 * @return void
 */
	protected function _put($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$subject = $this->_subject(['id' => $id]);

		$entity = $this->_findRecord($id, $subject);
		if (!$entity) {
			return $this->_notFound($id);
		}

		$subject->set(['item' => $entity]);

		$entity->accessible('*', true);
		$entity->set($this->_request()->data);

		$this->_trigger('beforeSave', $subject);
		if (call_user_func([$this->_repository(), $this->saveMethod()], $entity, $this->saveOptions())) {
			$subject->set(['success' => true, 'created' => false]);

			$this->setFlash('success', $subject);
			$this->_trigger('afterSave', $subject);

			return $this->_redirect($subject, ['action' => 'index']);
		}

		$subject->set(['success' => false, 'created' => false]);
		$this->setFlash('error', $subject);
		$this->_trigger('afterSave', $subject);
		$this->_trigger('beforeRender', $subject);
	}

/**
 * Find a record from the ID
 *
 * @param string $id
 * @param string $findMethod
 * @return array
 */
	protected function _findRecord($id, Subject $subject) {
		$repository = $this->_repository();
		$query = $repository->find();
		$query->where([$repository->primaryKey() => $id]);

		$subject->set(['repository' => $repository, 'query' => $query]);

		$this->_trigger('beforeFind', $subject);
		return $query->first();
	}

/**
 * Throw exception if a record is not found
 *
 * @throws Exception
 * @param string $id
 * @return void
 */
	protected function _notFound($id) {
		$this->_trigger('recordNotFound', compact('id'));

		$message = $this->message('recordNotFound', compact('id'));
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
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
 * Is the passed ID valid?
 *
 * Validate the id in the URL (the parent function) and then validate the id in the data.
 *
 * The data-id check is independent of the config setting `validateId`; this checks whether
 * the id in the URL matches the id in the submitted data (a type insensitive check). If
 * the id is different, this probably indicates a malicious form submission, attempting
 * to add/edit a record the user doesn't have permission for by submitting to a URL they
 * do have permission to access
 *
 * @param mixed $id
 * @return boolean
 * @throws BadRequestException If id is invalid
 */
	protected function _validateId($id) {
		parent::_validateId($id);

		$request = $this->_request();
		if (!$request->data) {
			return true;
		}

		$dataId = null;
		$repository = $this->_repository();

		$dataId = $request->data($repository->alias() . '.' . $repository->primaryKey()) ?: $request->data($repository->primaryKey());
		if ($dataId === null) {
			return true;
		}

		// deliberately type insensitive
		if ($dataId == $id) {
			return true;
		}

		$this->_trigger('invalidId', ['id' => $dataId]);

		$message = $this->message('invalidId');
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
	}

}
