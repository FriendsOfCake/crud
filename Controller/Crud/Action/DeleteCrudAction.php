<?php

App::uses('Hash', 'Utility');
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
		'view' => null,
		'validateId' => null,
		'requestType' => 'default',
		'requestMethods' => array(
			'default' => array('get', 'delete'),
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
 * Change the name of the view variable name
 * of the data when its sent to the view
 *
 * @param mixed $method
 * @return mixed
 */
	public function viewVar($name = null) {
		if (empty($name)) {
			return $this->config('viewVar') ?: Inflector::variable($this->_model()->name);
		}

		return $this->config('viewVar', $name);
	}

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

		if (!$this->__validate($id)) {
			$controller = $this->_controller();
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		$request = $this->_request();
		if ($request->is('get')) {
			return $this->__handle($id);
		}

		$subject = $this->_trigger('beforeDelete', compact('id'));
		if ($subject->stopped) {
			$this->setFlash('error');
			$controller = $this->_controller();
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		$model = $this->_model();
		if ($model->delete($id)) {
			$this->setFlash('success');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => true));
		} else {
			$this->setFlash('error');
			$subject = $this->_trigger('afterDelete', array('id' => $id, 'success' => false));
		}

		$controller = $this->_controller();
		return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
	}

	protected function __handle($id) {
		$model = $this->_model();
		$request = $this->_model();

		$item = $request->data;
		$subject = $this->_trigger('afterFind', compact('id', 'item'));
		$request->data = Hash::merge(array($model->alias => array(
			$model->primaryKey => $id
		)), $item, $model->data, $subject->item);

		$this->_controller()->set(array($this->viewVar() => $request->data));
		$this->_trigger('beforeRender', compact('id', 'item'));
		return;
	}

	protected function __validate($id) {
		$model = $this->_model();

		$query = array();
		$query['conditions'] = array($model->escapeField() => $id);

		$findMethod = $this->_getFindMethod('count');
		$subject = $this->_trigger('beforeFind', compact('id', 'query', 'findMethod'));
		$query = $subject->query;

		$count = $model->find($findMethod, $query);
		if (empty($count)) {
			$this->_trigger('recordNotFound', compact('id'));

			$message = $this->message('recordNotFound', array('id' => $id));
			$exceptionClass = $message['class'];
			throw new $exceptionClass($message['text'], $message['code']);
		}

		return true;
	}

}
