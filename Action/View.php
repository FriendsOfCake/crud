<?php

namespace Crud\Action;

use \Cake\Utility\Inflector;

/**
 * Handles 'View' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class View extends Base {

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
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'all',
		'view' => null,
		'viewVar' => null,
		'serialize' => array()
	);

/**
 * Constant representing the scope of this action
 *
 * @var integer
 */
	const ACTION_SCOPE = Base::SCOPE_RECORD;

/**
 * Change the name of the view variable name
 * of the data when its sent to the view
 *
 * @param mixed $name
 * @return mixed
 */
	public function viewVar($name = null) {
		if (empty($name)) {
			return $this->config('viewVar') ?: Inflector::variable($this->_model()->alias());
		}

		return $this->config('viewVar', $name);
	}

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

		$table = $this->_repository();
		$query = $table->find($this->_getFindMethod());
		$query->where([$table->primaryKey() => $id]);

		$subject = $this->_trigger('beforeFind', compact('id', 'query'));

		$item = $query->first();
		if (empty($item)) {
			$this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', compact('id'));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$success = true;
		$viewVar = $this->viewVar();

		$subject = $this->_trigger('afterFind', compact('id', 'viewVar', 'success', 'item'));

		$this->_controller()->set(['success' => $subject->success, $subject->viewVar => $subject->item]);
		$this->_trigger('beforeRender', $subject);
	}

}
