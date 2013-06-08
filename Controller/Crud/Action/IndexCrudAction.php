<?php

App::uses('CrudAction', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * Handles 'Index' Crud actions
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class IndexCrudAction extends CrudAction {

/**
 * Default settings for 'index' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * @var array
 */
	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'all',
		'view' => null,
		'viewVar' => 'items',
		'serialize' => array(
			'success',
			'items' => 'data'
		)
	);

/**
 * Generic index action
 *
 * Triggers the following callbacks
 *	- Crud.init
 *	- Crud.beforePaginate
 *	- Crud.afterPaginate
 *	- Crud.beforeRender
 *
 * @return void
 */
	protected function _handle() {
		// Compute the pagination settings
		$this->_computePaginationConfig();

		// Do the pagination
		$items = $this->_controller->paginate($this->_model);

		$subject = $this->_crud->trigger('afterPaginate', compact('items'));
		$items = $subject->items;

		// Make sure to cast any iterators to array
		if ($items instanceof Iterator) {
			$items = iterator_to_array($items);
		}

		$this->_controller->set(array('success' => true, $this->viewVar() => $items));
		$this->_crud->trigger('beforeRender');
	}

/**
 * Compute pagination settings
 *
 * @return void
 */
	protected function _computePaginationConfig() {
		// Ensure we have Paginator loaded
		if (!isset($this->_controller->Paginator)) {
			$this->_controller->Paginator = $this->_collection->load('Paginator');
		}
		$Paginator = $this->_controller->Paginator;
		$settings = $Paginator->settings;

		// Copy pagination settings from the controller
		if (!empty($this->_controller->paginate)) {
			$settings = array_merge($settings, $this->_controller->paginate);
		}

		if (!empty($settings[$this->_modelClass]['findType'])) {
			$findMethod = $settings[$this->_modelClass]['findType'];
		} elseif (!empty($settings['findType'])) {
			$findMethod = $settings['findType'];
		} else {
			$findMethod = $this->_getFindMethod('all');
		}

		$subject = $this->_crud->trigger('beforePaginate', compact('findMethod'));

		// Copy pagination settings from the controller
		if (!empty($this->_controller->paginate)) {
			$settings = array_merge($settings, $Paginator->settings, $this->_controller->paginate);
		}

		// If pagination settings is using ModelAlias modify that
		if (!empty($settings[$this->_modelClass])) {
			$settings[$this->_modelClass]['findType'] = $subject->findMethod;
		} else { // Or just work directly on the root key
			$settings['findType'] = $subject->findMethod;
		}

		$Paginator->settings = $settings;
		$this->_controller->paginate = $settings;
	}

}
