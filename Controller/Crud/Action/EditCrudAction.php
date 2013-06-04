<?php

App::uses('Hash', 'Utility');
App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Edit' Crud actions
 *
 */
class EditCrudAction extends CrudAction {

/**
 * Default settings for 'edit' actions
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
 * Generic edit action
 *
 * Triggers the following callbacks
 *	- Crud.init
 *	- Crud.beforeSave
 *	- Crud.afterSave
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

		$this->_validateId($id);

		if ($this->_request->is('put')) {
			$this->_crud->trigger('beforeSave', compact('id'));
			if ($this->_model->saveAll($this->_request->data, $this->saveOptions())) {
				$this->setFlash('update.success');
				$subject = $this->_crud->trigger('afterSave', array('id' => $id, 'success' => true));
				return $this->_redirect($subject, array('action' => 'index'));
			} else {
				$this->setFlash('update.error');
				$this->_crud->trigger('afterSave', array('id' => $id, 'success' => false));
			}
		} else {
			$query = array();
			$query['conditions'] = array($this->_model->escapeField() => $id);
			$findMethod = $this->_getFindMethod('first');
			$subject = $this->_crud->trigger('beforeFind', compact('query', 'findMethod'));
			$query = $subject->query;

			$this->_request->data = $this->_model->find($subject->findMethod, $query);
			if (empty($this->_request->data)) {
				$subject = $this->_crud->trigger('recordNotFound', compact('id'));
				$this->setFlash('find.error');
				return $this->_redirect($subject, array('action' => 'index'));
			}

			$this->_crud->trigger('afterFind', compact('id'));

			// Make sure to merge any changed data in the model into the post data
			$this->_request->data = Hash::merge($this->_request->data, $this->_model->data);
		}

		// Trigger a beforeRender
		$this->_crud->trigger('beforeRender');
	}

}
