<?php

App::uses('Hash', 'Utility');
App::uses('CrudAction', 'Crud.Controller/Crud/Action');

/**
 * Handles 'Add' Crud actions
 *
 */
class AddCrudAction extends CrudAction {

/**
 * Default settings for 'add' actions
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'first',
		'view' => null,
		'relatedLists' => true,
		'validateId' => null,
		'saveOptions' => array(
			'validate' => 'first',
			'atomic' => true
		)
	);

/**
 * Generic add action
 *
 * Triggers the following callbacks
 *	- Crud.init
 *	- Crud.beforeSave
 *	- Crud.afterSave
 *	- Crud.beforeRender
 *
 * @return void
 */
	protected function _handle() {
		if (!$this->_request->is('post')) {
			$this->_crud->trigger('beforeRender', array('success' => false));
			return;
		}

		$this->_crud->trigger('beforeSave');
		if ($this->_model->saveAll($this->_request->data, $this->saveOptions())) {
			$this->setFlash('create.success');
			$subject = $this->_crud->trigger('afterSave', array('success' => true, 'id' => $this->_model->id));
			return $this->_redirect($subject, array('action' => 'index'));
		}

		$this->setFlash('create.error');
		$this->_crud->trigger('afterSave', array('success' => false));
		// Make sure to merge any changed data in the model into the post data
		$this->_request->data = Hash::merge($this->_request->data, $this->_model->data);
	}

}
