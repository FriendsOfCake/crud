<?php

App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class DeleteCrudAction extends CrudAction {

/**
 * Default settings for 'add' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `secureDelete` delete() can only be called with the HTTP DELETE verb, not POST when `true`.
 * If set to `false` HTTP POST is also acceptable
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'count',
		'secureDelete' => true,
		'flash' => array(
			'success' => array(
				'message' => 'Successfully deleted {name}'
			),
			'error' => array(
				'message' => 'Could not delete {name}'
			)
		)
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
 * @throws NotFoundException If record not found
 * @throws BadRequestException If secure delete enabled and not a HTTP DELETE request
 */
	protected function _handle($id = null) {
		if (empty($id)) {
			$id = $this->getIdFromRequest();
		}

		$this->_validateId($id);

		if (!$this->_request->is('delete') &&
			!($this->_request->is('post') &&
			false === $this->config('secureDelete'))
		) {
			$subject = $this->_crud->getSubject(compact('id'));
			throw new BadRequestException('invalid_http_request.error');
		}

		$query = array();
		$query['conditions'] = array($this->_model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('count');
		$subject = $this->_crud->trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $this->_model->find($subject->findMethod, $query);
		if (empty($count)) {
			$subject = $this->_crud->trigger('recordNotFound', compact('id'));
			throw new NotFoundException('find.error');
		}

		$subject = $this->_crud->trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->setFlash('error');
			return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
		}

		if ($this->_model->delete($id)) {
			$this->setFlash('success');
			$subject = $this->_crud->trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->setFlash('error');
			$subject = $this->_crud->trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		return $this->_redirect($subject, $this->_controller->referer(array('action' => 'index')));
	}
}
