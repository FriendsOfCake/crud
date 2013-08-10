<?php

App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'View' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ViewCrudAction extends CrudAction {

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
		'findMethod' => 'first',
		'view' => null,
		'viewVar' => null,
		'serialize' => array()
	);

/**
 * Change the name of the view variable name
 * of the data when its sent to the view
 *
 * @param mixed $method
 * @return mixed
 */
	public function viewVar($name = null) {
		if (empty($name)) {
			return $this->config('viewVar') ?: Inflector::variable($this->_model()->name);
		}

		return $this->config('viewVar', $name);
	}

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

		$model = $this->_model();

		$query = array();
		$query['conditions'] = array($model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('first');
		$subject = $this->_trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$item = $model->find($subject->findMethod, $query);

		if (empty($item)) {
			$this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', array('id' => $id));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject = $this->_trigger('afterFind', compact('id', 'item'));
		$item = $subject->item;

		$this->_controller()->set(array('success' => true, $this->viewVar() => $item));
		$this->_trigger('beforeRender', compact('id', 'item'));
	}

}
