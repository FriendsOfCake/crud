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
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * `relatedModels` is a map of the controller action and the whether it should fetch associations lists
 * to be used in select boxes. An array as value means it is enabled and represent the list
 * of model associations to be fetched
 *
 * `saveOptions` Raw array passed as 2nd argument to saveAll() in `add` and `edit` method
 * If you configure a key with your action name, it will override the default settings.
 * This is useful for adding fieldList to enhance security in saveAll.
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'view' => null,
		'relatedModels' => true,
		'saveOptions' => array(
			'validate' => 'first',
			'atomic' => true
		),
		'requestMethods' => array('get', 'post'),
		'messages' => array(
			'success' => array(
				'text' => 'Successfully created {name}'
			),
			'error' => array(
				'text' => 'Could not create {name}'
			)
		),
		'serialize' => array()
	);

/**
 * Generic add action
 *
 * Triggers the following callbacks
 *	- Crud.initialize
 *	- Crud.beforeSave
 *	- Crud.afterSave
 *	- Crud.beforeRender
 *
 * @return void
 */
	protected function _handle() {
		$request = $this->_request();
		$model = $this->_model();

		if (!$request->is('post')) {
			$model->create();
			$request->data = $model->data;
			$this->_trigger('beforeRender', array('success' => false));
			return;
		}

		$this->_trigger('beforeSave');
		if ($model->saveAll($request->data, $this->saveOptions())) {
			$this->setFlash('success');
			$subject = $this->_trigger('afterSave', array('success' => true, 'created' => true,	'id' => $model->id));
			return $this->_redirect($subject, array('action' => 'index'));
		}

		$this->setFlash('error');
		$this->_trigger('afterSave', array('success' => false, 'created' => false));
		$this->_request()->data = Hash::merge($request->data, $model->data);
		$this->_trigger('beforeRender', array('success' => false));
	}

}
