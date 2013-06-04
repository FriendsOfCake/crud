<?php

App::uses('CrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudSubject', 'Crud.Controller');

class AddCrudAction extends CrudAction {

	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'first',
		'view' => 'add',
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
		if ($this->_request->is('post')) {
			$this->_Crud->trigger('beforeSave');
			if ($this->_model->saveAll($this->_request->data, $this->_getSaveAllOptions())) {
				$this->setFlash('create.success');
				$subject = $this->_Crud->trigger('afterSave', array('success' => true, 'id' => $this->_model->id));
				return $this->_redirect($subject, array('action' => 'index'));
			} else {
				$this->setFlash('create.error');
				$this->_Crud->trigger('afterSave', array('success' => false));
				// Make sure to merge any changed data in the model into the post data
				$this->_request->data = Set::merge($this->_request->data, $this->_model->data);
			}
		}

		$this->_Crud->trigger('beforeRender', array('success' => false));
	}

}
