<?php
namespace Crud\Action;

use Crud\Event\Subject;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Delete extends Base {

	use RedirectTrait;

/**
 * Default settings for 'add' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'findMethod' => 'count',
		'messages' => [
			'success' => [
				'text' => 'Successfully deleted {name}'
			],
			'error' => [
				'text' => 'Could not delete {name}'
			]
		],
		'api' => [
			'success' => [
				'code' => 200
			],
			'error' => [
				'code' => 400
			]
		]
	];

/**
 * HTTP DELETE handler
 *
 * @throws NotFoundException If record not found
 * @param string $id
 * @return void
 */
	protected function _delete($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$subject = $this->_subject(['id' => $id]);
		$entity = $this->_findRecord($id, $subject);
		if (!$entity) {
			$this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', ['id' => $id]);
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject->set(['item' => $entity]);

		$event = $this->_trigger('beforeDelete', $subject);
		if ($event->stopped) {
			$this->setFlash('error');
			return $this->_redirect($subject, ['action' => 'index']);
		}

		if ($this->_repository()->delete($entity)) {
			$subject->set(['success' => true]);

			$this->setFlash('success', $subject);
			$this->_trigger('afterDelete', $subject);
		} else {
			$subject->set(['success' => false]);

			$this->setFlash('error', $subject);
			$this->_trigger('afterDelete', $subject);
		}

		$this->_redirect($subject, ['action' => 'index']);
	}

/**
 * HTTP POST handler
 *
 * @param mixed $id
 * @return void
 */
	protected function _post($id = null) {
		return $this->_delete($id);
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

}
