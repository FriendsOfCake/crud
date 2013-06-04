<?php

App::uses('CrudAction', 'Crud.Controller/Crud/Action');

/**
 * Handles 'Delete' Crud actions
 *
 */
class DeleteCrudAction extends CrudAction {

/**
 * Default settings for 'delete' actions
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'count',
		'secureDelete' => true
	);

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
	protected function _handle($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}

		$this->_validateId($id);

		if (!$this->_request->is('delete') && !($this->_request->is('post') && false === $this->config('secureDelete'))) {
			$subject = $this->_crud->getSubject(compact('id'));
			$this->setFlash('invalid_http_request.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('count');
		$subject = $this->_crud->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $this->_model->find($subject->findMethod, $query);
		if (empty($count)) {
			$subject = $this->_crud->trigger('recordNotFound', compact('id'));
			$this->setFlash('find.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		$subject = $this->_crud->trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->setFlash('delete.error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		if ($this->_model->delete($id)) {
			$this->setFlash('delete.success');
			$subject = $this->_crud->trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->setFlash('delete.error');
			$subject = $this->_crud->trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
	}
}
