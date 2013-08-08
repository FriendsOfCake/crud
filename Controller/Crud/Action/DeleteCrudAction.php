<?php

App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteCrudAction extends CrudAction {

/**
 * Default settings for 'add' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'count',
		'requestType' => 'default',
		'requestMethods' => array(
			'default' => array('delete'),
			'api' => array('delete')
		),

		'messages' => array(
			'success' => array(
				'text' => 'Successfully deleted {name}'
			),
			'error' => array(
				'text' => 'Could not delete {name}'
			)
		)
	);

/**
 * HTTP DELETE handler
 *
 * @throws NotFoundException If record not found
 * @param string $id
 * @return void
 */
	protected function _delete($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$model = $this->_model();

		$query = array();
		$query['conditions'] = array($model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('count');
		$subject = $this->_trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $model->find($subject->findMethod, $query);
		if (empty($count)) {
			$this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', array('id' => $id));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject = $this->_trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->setFlash('error');
			$controller = $this->_controller();
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		if ($model->delete($id)) {
			$this->setFlash('success');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->setFlash('error');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		$controller = $this->_controller();
		$this->_redirect($subject, $controller->referer(array('action' => 'index')));
	}

/**
 * HTTP POST handler
 *
 * @param mixed $id
 * @return void
 */
	protected function _post($id) {
		return $this->_delete($id);
	}

}
