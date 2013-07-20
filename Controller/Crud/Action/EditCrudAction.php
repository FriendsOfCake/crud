<?php

App::uses('Hash', 'Utility');
App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Edit' Crud actions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
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
		'serialize' => array()
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
 * @throws NotFoundException If record not found
 */
	protected function _handle($id = null) {
		if (!$this->_validateId($id)) {
			return false;
		}

		if ($this->_request->is('put')) {
			$this->_crud->trigger('beforeSave', compact('id'));
			if ($this->_model->saveAll($this->_request->data, $this->saveOptions())) {
				$this->setFlash('success');
				$subject = $this->_crud->trigger('afterSave', array('id' => $id, 'success' => true, 'created' => false));
				return $this->_redirect($subject, array('action' => 'index'));
			} else {
				$this->setFlash('error');
				$this->_crud->trigger('afterSave', array('id' => $id, 'success' => false, 'created' => false));
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

				$message = $this->message('recordNotFound', array('id' => $subject->id));
				$exceptionClass = $message['class'];
				throw new $exceptionClass($message['text'], $message['code']);
			}

			$this->_crud->trigger('afterFind', compact('id'));
			$this->_request->data = Hash::merge($this->_request->data, $this->_model->data);
		}

		$this->_crud->trigger('beforeRender');
	}

}
