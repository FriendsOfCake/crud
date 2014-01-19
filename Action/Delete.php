<?php
namespace Crud\Action;

use Crud\Event\Subject;
use Crud\Traits\RedirectTrait;
use Crud\Traits\FindMethodTrait;

/**
 * Handles 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Delete extends Base {

	use FindMethodTrait;
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

		$event = $this->_trigger('beforeDelete', $subject);
		if ($event->isStopped()) {
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

}
