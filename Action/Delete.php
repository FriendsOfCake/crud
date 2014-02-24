<?php
namespace Crud\Action;

use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;

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
		'scope' => 'entity',
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
 * @param string $id
 * @return void
 */
	protected function _handle($id = null) {
		$subject = $this->_subject();
		$subject->set(['id' => $id]);

		$entity = $this->_findRecord($id, $subject);

		$event = $this->_trigger('beforeDelete', $subject);
		if ($event->isStopped()) {
			return $this->_stopped($subject);
		}

		if ($this->_repository()->delete($entity)) {
			$this->_success($subject);
		} else {
			$this->_error($subject);
		}

		$this->_redirect($subject, ['action' => 'index']);
	}

/**
 * Success callback
 *
 * @param  Subject $subject
 * @return void
 */
	protected function _success(Subject $subject) {
		$subject->set(['success' => true]);

		$this->setFlash('success', $subject);

		$this->_trigger('afterDelete', $subject);
	}

/**
 * Error callback
 *
 * @param  Subject $subject
 * @return void
 */
	protected function _error(Subject $subject) {
		$subject->set(['success' => false]);

		$this->setFlash('error', $subject);

		$this->_trigger('afterDelete', $subject);
	}

/**
 * Stopped callback
 *
 * @param  Subject $subject
 * @return \Cake\Network\Response
 */
	protected function _stopped(Subject $subject) {
		$this->setFlash('error');
		return $this->_redirect($subject, ['action' => 'index']);
	}

}
