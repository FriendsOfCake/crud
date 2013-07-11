<?php

App::uses('CrudAction', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * Handles 'View' Crud actions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
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
			return $this->config('viewVar') ?: Inflector::variable($this->_model->name);
		}

		return $this->config('viewVar', $name);
	}

/**
 * Generic view action
 *
 * Triggers the following callbacks
 *	- Crud.init
 *	- Crud.beforeFind
 *	- Crud.recordNotFound
 *	- Crud.afterFind
 *	- Crud.beforeRender
 *
 * @param string $id
 * @return void
 * @throws NotFoundException If record not found
 */
	protected function _handle($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}

		if (!$this->_validateId($id)) {
			return false;
		}

		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('first');
		$subject = $this->_crud->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$item = $this->_model->find($subject->findMethod, $query);

		if (empty($item)) {
			$subject = $this->_crud->trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', array('id' => $subject->id));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject = $this->_crud->trigger('afterFind', compact('id', 'item'));
		$item = $subject->item;

		$this->_controller->set(array('success' => true, $this->viewVar() => $item));
		$this->_crud->trigger('beforeRender', compact('id', 'item'));
	}

}
