<?php

App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditCrudAction extends CrudAction {

/**
 * Default settings for 'edit' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * `relatedModels` is a map of the controller action and the whether it should fetch associations lists
 * to be used in select boxes. An array as value means it is enabled and represent the list
 * of model associations to be fetched
 *
 * `validateId` ID Argument validation - by default it will inspect your model's primary key
 * and based on it's data type either use integer or uuid validation.
 * Can be disabled by setting it to "false". Supports "integer" and "uuid" configuration
 * By default it's configuration is NULL, which means "auto detect"
 *
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'first',
		'view' => null,
		'relatedModels' => true,
		'validateId' => null,
		'saveOptions' => array(
			'validate' => 'first',
			'atomic' => true
		),
		'messages' => array(
			'success' => array(
				'text' => 'Successfully updated {name}'
			),
			'error' => array(
				'text' => 'Could not update {name}'
			)
		),
		'api' => array(
			'methods' => array('put', 'post'),
			'success' => array(
				'code' => 200
			),
			'error' => array(
				'exception' => array(
					'type' => 'validate',
					'class' => 'CrudValidationException'
				)
			)
		),
		'serialize' => array()
	);

/**
 * Constant representing the scope of this action
 *
 * @var integer
 */
	const ACTION_SCOPE = CrudAction::SCOPE_RECORD;

/**
 * HTTP GET handler
 *
 * @throws NotFoundException If record not found
 * @param string $id
 * @return void
 */
	protected function _get($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$request = $this->_request();
		$model = $this->_model();

		$request->data = $this->_findRecord($id);
		if (empty($request->data)) {
			return $this->_notFound($id);
		}

		$item = $request->data;
		$subject = $this->_trigger('afterFind', compact('id', 'item'));
		$request->data = Hash::merge($request->data, $model->data, $subject->item);

		$this->_trigger('beforeRender');
	}

/**
 * HTTP PUT handler
 *
 * @param mixed $id
 * @return void
 */
	protected function _put($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		$request = $this->_request();
		$model = $this->_model();
		$model->id = $id;

		$existing = $this->_findRecord($id, 'count');
		if (empty($existing)) {
			return $this->_notFound($id);
		}

		if ($request->data('_cancel')) {
			$subject = $this->_trigger('beforeCancel', array('id' => $id));
			$controller = $this->_controller();
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		$this->_trigger('beforeSave', compact('id'));
		if ($model->saveAll($request->data, $this->saveOptions())) {
			$this->setFlash('success');
			$subject = $this->_trigger('afterSave', array('id' => $id, 'success' => true, 'created' => false));

			if ($request->data('_add')) {
				return $this->_redirect($subject, array('action' => 'add'));
			} elseif ($request->data('_edit')) {
				return $this->_redirect($subject, array('action' => $request->action, $id));
			}

			$controller = $this->_controller();
			return $this->_redirect($subject, $controller->referer(array('action' => 'index')));
		}

		$this->setFlash('error');
		$subject = $this->_trigger('afterSave', array('id' => $id, 'success' => false, 'created' => false));
		$this->_trigger('beforeRender', $subject);
	}

/**
 * Find a record from the ID
 *
 * @param string $id
 * @param $findMethod
 * @return array
 */
	protected function _findRecord($id, $findMethod = 'first') {
		$model = $this->_model();

		$query = array();
		$query['conditions'] = array($model->escapeField() => $id);

		$findMethod = $this->_getFindMethod($findMethod);
		$subject = $this->_trigger('beforeFind', compact('query', 'findMethod'));

		return $model->find($subject->findMethod, $subject->query);
	}

/**
 * Throw exception if a record is not found
 *
 * @throws Exception
 * @param string $id
 * @return void
 */
	protected function _notFound($id) {
		$this->_trigger('recordNotFound', compact('id'));

		$message = $this->message('recordNotFound', compact('id'));
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
	}

/**
 * HTTP POST handler
 *
 * Thin proxy for _put
 *
 * @param mixed $id
 * @return void
 */
	protected function _post($id = null) {
		return $this->_put($id);
	}

}
