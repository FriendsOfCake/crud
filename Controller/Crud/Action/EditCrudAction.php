<?php

App::uses('CrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudSubject', 'Crud.Controller');

class EditCrudAction extends CrudAction {

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
	protected function _handle() {
		if ($this->_settings['type'] !== 'edit') {
			return;
		}

		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}
		$this->_validateId($id);

		if ($this->_request->is('put')) {
			$this->_Crud->trigger('beforeSave', compact('id'));
			if ($this->_model->saveAll($this->_request->data, $this->_getSaveAllOptions())) {
				$this->_Crud->setFlash('update.success');
				$subject = $this->_Crud->trigger('afterSave', array('id' => $id, 'success' => true));
				return $this->_redirect($subject, array('action' => 'index'));
			} else {
				$this->_Crud->setFlash('update.error');
				$this->_Crud->trigger('afterSave', array('id' => $id, 'success' => false));
			}
		} else {
			$query = array();
			$query['conditions'] = array($this->_model->escapeField() => $id);
			$findMethod = $this->_getFindMethod(null, 'first');
			$subject = $this->_Crud->trigger('beforeFind', compact('query', 'findMethod'));
			$query = $subject->query;

			$this->_request->data = $this->_model->find($subject->findMethod, $query);
			if (empty($this->_request->data)) {
				$subject = $this->_Crud->trigger('recordNotFound', compact('id'));
				$this->_Crud->setFlash('find.error');
				return $this->_redirect($subject, array('action' => 'index'));
			}

			$this->_Crud->trigger('afterFind', compact('id'));

			// Make sure to merge any changed data in the model into the post data
			$this->_request->data = Set::merge($this->_request->data, $this->_model->data);
		}

		// Trigger a beforeRender
		$this->_Crud->trigger('beforeRender');
	}

}
