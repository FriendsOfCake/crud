<?php

App::uses('CrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudSubject', 'Crud.Controller');

class DeleteCrudAction extends CrudAction {

/**
 * Generic delete action
 *
 * Triggers the following callbacks
 *	- beforeFind
 *	- recordNotFound
 *	- beforeDelete
 *	- afterDelete
 *
 * @param string $id
 * @return void
 */
	protected function _handle() {
		if ($this->_settings['type'] !== 'delete') {
			return;
		}

		$id = $this->getIdFromRequest();
		$this->_validateId($id);

		if (!$this->_request->is('delete') && !($this->_request->is('post') && false === $this->_Crud->config('secureDelete'))) {
			$subject = $this->_Crud->getSubject(compact('id'));
			$this->_Crud->setFlash('invalid_http_request.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod(null, 'count');
		$subject = $this->_Crud->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $this->_model->find($subject->findMethod, $query);
		if (empty($count)) {
			$subject = $this->_Crud->trigger('recordNotFound', compact('id'));
			$this->_Crud->setFlash('find.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		$subject = $this->_Crud->trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->_Crud->setFlash('delete.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		if ($this->_model->delete($id)) {
			$this->_Crud->setFlash('delete.success');
			$subject = $this->_Crud->trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->_Crud->setFlash('delete.error');
			$subject = $this->_Crud->trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
	}
}
