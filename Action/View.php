<?php
namespace Crud\Action;

use Cake\Utility\Inflector;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'View' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class View extends Base {

	use FindMethodTrait;
	use ViewTrait;
	use ViewVarTrait;

/**
 * Default settings for 'view' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'findMethod' => 'all',
		'view' => null,
		'viewVar' => null,
		'serialize' => []
	];

/**
 * HTTP GET handler
 *
 * @param string $id
 * @return void
 * @throws NotFoundException If record not found
 */
	protected function _get($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$subject = $this->_subject(['id' => $id]);
		$item = $this->_findRecord($id, $subject);
		if (empty($item)) {
			$this->_trigger('recordNotFound', $subject);

			$message = $this->message('recordNotFound', ['id' => $id]);
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject->set(['item' => $item, 'success' => true, 'viewVar' => $this->viewVar()]);
		$this->_trigger('afterFind', compact('id', 'viewVar', 'success', 'item'));

		$this->_controller()->set(['success' => $subject->success, $subject->viewVar => $subject->item]);
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

}
