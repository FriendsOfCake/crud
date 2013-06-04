<?php

App::uses('CrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudSubject', 'Crud.Controller');

class IndexCrudAction extends CrudAction {

	protected $_settings = array(
		'enabled' => true,
		'findMethod' => 'all',
		'view' => 'index'
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
		$Paginator = $this->_Collection->load('Paginator');

		// Copy pagination settings from the controller
		if (!empty($this->_controller->paginate)) {
			$Paginator->settings = array_merge($Paginator->settings, $this->_controller->paginate);
		}

		if (!empty($Paginator->settings[$this->_modelName]['findType'])) {
			$findMethod = $Paginator->settings[$this->_modelName]['findType'];
		} elseif (!empty($Paginator->settings['findType'])) {
			$findMethod = $Paginator->settings['findType'];
		} else {
			$findMethod = $this->_getFindMethod('all');
		}

		$subject = $this->_Crud->trigger('beforePaginate', compact('findMethod'));

		// Copy pagination settings from the controller
		if (!empty($this->_controller->paginate)) {
			$Paginator->settings = array_merge($Paginator->settings, $this->_controller->paginate);
		}

		// If pagination settings is using ModelAlias modify that
		if (!empty($Paginator->settings[$this->_modelName])) {
			$Paginator->settings[$this->_modelName][0] = $subject->findMethod;
			$Paginator->settings[$this->_modelName]['findType'] = $subject->findMethod;
		} else { // Or just work directly on the root key
			$Paginator->settings[0] = $subject->findMethod;
			$Paginator->settings['findType'] = $subject->findMethod;
		}

		// Push the paginator settings back to Controller
		$this->_controller->paginate = $Paginator->settings;

		// Do the pagination
		$items = $this->_controller->paginate($this->_model);

		$subject = $this->_Crud->trigger('afterPaginate', compact('items'));
		$items = $subject->items;

		// Make sure to cast any iterators to array
		if ($items instanceof Iterator) {
			$items = iterator_to_array($items);
		}

		$this->_controller->set(compact('items'));
		$this->_Crud->trigger('beforeRender');
	}

}
