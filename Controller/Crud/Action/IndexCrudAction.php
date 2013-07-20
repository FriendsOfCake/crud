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
		'viewVar' => null,
		'serialize' => array()
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
			return $this->config('viewVar') ?: Inflector::variable($this->_controller->name);
		}

		return $this->config('viewVar', $name);
	}

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
		if (!isset($this->_controller->Paginator)) {
			$this->_controller->Paginator = $this->_collection->load('Paginator', $this->_controller->paginate);
		}

		$Paginator = $this->_controller->Paginator;
		$settings = &$Paginator->settings;

		if (isset($settings[$this->_modelClass]) && empty($settings[$this->_modelClass]['findType'])) {
			$settings[$this->_modelClass]['findType'] = $this->_getFindMethod('all');
		} elseif (empty($settings['findType'])) {
			$settings['findType'] = $this->_getFindMethod('all');
		}

		$this->_crud->trigger('beforePaginate', array('paginator' => $Paginator));
	}

}
