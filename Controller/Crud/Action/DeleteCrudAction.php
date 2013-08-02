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
 * @throws MethodNotAllowedException If secure delete enabled and not a HTTP DELETE request
 */
	protected function _handle($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$request = $this->_request();

		$validRequest = $request->is('delete');
		if (!$validRequest) {
			$permitPost = !$this->config('secureDelete');
			$validRequest = ($request->is('post') && $permitPost);
		}

		if (!$validRequest) {
			$subject = $this->_subject(compact('id'));

			$methods = 'DELETE';
			if ($permitPost) {
				$methods .= 'or POST';
			}

			$message = $this->message('badRequestMethod', array('id' => $subject->id, 'methods' => $methods));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$model = $this->_model();
		$controller = $this->_controller();

		$query = array();
		$query['conditions'] = array($model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('count');
		$subject = $this->_trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $model->find($subject->findMethod, $query);
		if (empty($count)) {
			$subject = $this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', array('id' => $subject->id));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		$subject = $this->_trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->setFlash('error');
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		if ($model->delete($id)) {
			$this->setFlash('success');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->setFlash('error');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
	}
}
