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
		'viewVar' => 'item',
		'serialize' => array()
	);

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
			$this->setFlash('find.error');
			return $this->_redirect($subject, array('action' => 'index'));
		}

		$subject = $this->_crud->trigger('afterFind', compact('id', 'item'));
		$item = $subject->item;

		$this->_controller->set(array('success' => true, $this->viewVar() => $item));
		$this->_crud->trigger('beforeRender', compact('id', 'item'));
	}

}
