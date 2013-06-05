<?php

App::uses('Hash', 'Utility');
App::uses('CrudAction', 'Crud.Controller/Crud');

/**
 * Handles 'Add' Crud actions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class AddCrudAction extends CrudAction {

/**
 * Default settings for 'add' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * `relatedLists` is a map of the controller action and the whether it should fetch associations lists
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
		'relatedLists' => true,
		'validateId' => null,
		'saveOptions' => array(
			'validate' => 'first',
			'atomic' => true
		)
	);

/**
 * Generic add action
 *
 * Triggers the following callbacks
 *	- Crud.init
 *	- Crud.beforeSave
 *	- Crud.afterSave
 *	- Crud.beforeRender
 *
 * @return void
 */
	protected function _handle() {
		if (!$this->_request->is('post')) {
			$this->_crud->trigger('beforeRender', array('success' => false));
			return;
		}

		$this->_crud->trigger('beforeSave');
		if ($this->_model->saveAll($this->_request->data, $this->saveOptions())) {
			$this->setFlash('create.success');
			$subject = $this->_crud->trigger('afterSave', array('success' => true, 'id' => $this->_model->id));
			return $this->_redirect($subject, array('action' => 'index'));
		}

		$this->setFlash('create.error');
		$this->_crud->trigger('afterSave', array('success' => false));
		// Make sure to merge any changed data in the model into the post data
		$this->_request->data = Hash::merge($this->_request->data, $this->_model->data);
	}

}
