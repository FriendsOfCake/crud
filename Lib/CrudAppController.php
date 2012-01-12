<?php
App::uses('CrudCollection', 'Crud.Lib');

abstract class CrudAppController extends Controller {

	/**
	* List of form callbacks for CRUD actions
	*
	* Each key is the one of the CRUD controller action
	*
	* Each value is a key / value pair of class => configuration items
	* Each class must extend BaseFormDecorator
	*
	* @platform
	* @var array
	*/
	public $formCallbacks = array(
		// Public callbacks
		'index'			=> array(),
		'add'			=> array(),
		'edit'			=> array(),
		'view'			=> array(),
		'delete'		=> array(),

		// Admin callbacks
		'admin_index'	=> array(),
		'admin_add'		=> array(),
		'admin_edit'	=> array(),
		'admin_view'	=> array(),
		'admin_delete'	=> array(),

		// Shared callbacks
		'common'		=> array(
			'Crud.Default'
		)
	);

	public function index() {
		return $this->_index();
	}

	public function admin_index() {
		return $this->_index();
	}

	protected function _index() {
		$CallbackCollection = $this->_getCallbackCollection();
		$CallbackCollection->trigger('beforePaginate', array($this));
		$this->set('items', $this->paginate());
	}

	public function add() {
		return $this->_add();
	}

	public function admin_add() {
		return $this->_add();
	}

	/**
	 * Default add action
	 *
	 * @abstract Overwrite in controller as needed
	 * @param uuid $id
	 */
	protected function _add() {
		$CallbackCollection = $this->_getCallbackCollection();

		if ($this->request->is('post') || $this->request->is('put')) {
			$CallbackCollection->trigger('beforeSave');
			if ($this->{$this->modelClass}->saveAll($this->request->data, array('validate' => 'first', 'atomic' => true))) {
				$CallbackCollection->trigger('afterSave', array(true, $this->{$this->modelClass}->id));

				$this->Session->setFlash(__d('common', 'Succesfully created %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/success');
				$this->redirect(array('action' => 'index'));
			} else {
				$CallbackCollection->trigger('afterSave', array(false));
				$this->Session->setFlash(__d('common', 'Could not create %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
			}
		}

		$CallbackCollection->trigger('beforeRender');
		if ($this->autoRender) {
			var_dump($this->isAdmin());
			var_dump($this->request->is('api'));
			if ($this->isAdmin() && !$this->request->is('api')) {
				return $this->render('admin_form');
			}

			return $this->render('form');
		}
	}

	public function edit($id = null) {
		return $this->_edit($id);
	}

	public function admin_edit($id = null) {
		return $this->_edit($id);
	}

	/**
	 * Default edit action
	 *
	 * @abstract Overwrite in controller as needed
	 * @param uuid $id
	 */
	protected function _edit($id = null) {
		$this->validateUUID($id);
		$CallbackCollection = $this->_getCallbackCollection();

		if ($this->request->is('post') || $this->request->is('put')) {
			$CallbackCollection->trigger('beforeSave');
			if ($this->{$this->modelClass}->saveAll($this->request->data, array('validate' => 'first', 'atomic' => true))) {
				$CallbackCollection->trigger('afterSave', array(true, $this->{$this->modelClass}->id));

				$this->Session->setFlash(__d('common', '%s was succesfully updated', ucfirst(Inflector::humanize($this->{$this->modelClass}->name))), 'flash/success');
				$this->redirect(array('action' => 'index'));
			} else {
				$CallbackCollection->trigger('afterSave', array(false));
				$this->Session->setFlash(__d('common', 'Could not update %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
			}
		} else {
			$query = array();
			$query['conditions'] = array($this->{$this->modelClass}->escapeField() => $id);
			$query = $CallbackCollection->trigger('beforeFind', array($query));

			$this->request->data = $this->{$this->modelClass}->find('first', $query);
			if (empty($this->request->data)) {
				$CallbackCollection->trigger('recordNotFound', array($id));
				$this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
				$this->redirect(array('action' => 'index'));
			}
			$this->request->data = $CallbackCollection->trigger('afterFind', array($this->request->data));
		}

		$CallbackCollection->trigger('beforeRender');
		if ($this->autoRender) {
			if ($this->isAdmin() && !$this->request->is('api')) {
				return $this->render('admin_form');
			}
			return $this->render('form');
		}
	}

	public function view($id = null) {
		return $this->_view($id);
	}

	public function admin_view($id = null) {
		return $this->_view($id);
	}

	/**
	* Generic view action
	*
	* Triggers the following callbacks
	*  - beforeFind
	*  - recordNotFound
	*  - afterFind
	*  - beforeRender
	*
	* @param string $id
	* @return void
	*/
	protected function _view($id = null) {
		// Validate ID parameter
		$this->validateUUID($id);

		// Initialize callback collection
		$CallbackCollection = $this->_getCallbackCollection();

		// Build conditions
		$query = array();
		$query['conditions'] = array($this->{$this->modelClass}->escapeField() => $id);
		$query = $CallbackCollection->trigger('beforeFind', array($query));

		// Try and find the database record
		$item = $this->{$this->modelClass}->find('first', $query);

		// We could not find any record match the conditions in query
		if (empty($item)) {
			$CallbackCollection->trigger('recordNotFound', array($id));

			$this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
			$this->redirect(array('action' => 'index'));
		}

		// We found a record, trigger an afterFind
		$item = $CallbackCollection->trigger('afterFind', array($item));

		// Push it to the view
		$this->set(compact('item'));

		// Trigger a beforeRender
		$CallbackCollection->trigger('beforeRender');

		// Render a view if applicable
		if ($this->autoRender) {
			if ($this->isAdmin() && !$this->request->is('api')) {
				return $this->render('admin_view');
			}
			return $this->render('view');
		}
	}

	public function delete($id = null) {
		return $this->_delete($id);
	}

	public function admin_delete($id = null) {
		return $this->_delete($id);
	}

	/**
	* Generic delete action
	*
	* Triggers the following callbacks
	*
	*/
	protected function _delete($id = null) {
		$this->validateUUID($id);
		$CallbackCollection = $this->_getCallbackCollection();

		$query = array();
		$query['conditions'] = array($this->{$this->modelClass}->escapeField() => $id);
		$query = $CallbackCollection->trigger('beforeFind', array($query));

		$count = $this->{$this->modelClass}->find('count', $query);
		if (empty($count)) {
			$CallbackCollection->trigger('recordNotFound', array($id));
			$this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
			$this->redirect(array('action' => 'index'));
		}

		$item = $CallbackCollection->trigger('beforeDelete', array($id));
		if ($this->{$this->modelClass}->delete($id)) {
			$CallbackCollection->trigger('afterDelete', array($id, true));
		} else {
			$CallbackCollection->trigger('afterDelete', array($id, false));
		}
		$this->redirect(array('action' => 'index'));
	}

	protected function _getCallbackCollection($function = null) {
		if (empty($function)) {
			$function = $this->action;
		}

		$CallbackCollection = new CrudCollection();
		$CallbackCollection->loadAll($this->formCallbacks['common']);
		$CallbackCollection->loadAll($this->formCallbacks[$function]);
		if ($this->request->is('api')) {
			$CallbackCollection->load('Api.Api');
		}
		if (method_exists($this, '_loadCallbackCollections')) {
			$CallbackCollection->loadAll($this->_loadCallbackCollections($function));
		}
		$CallbackCollection->trigger('init', array($this, $function));

		return $CallbackCollection;
	}

	/**
	* Is this an admin request?
	*
	* @return boolean
	*/
	public function isAdmin() {
		return isset($this->request->params['admin']) && $this->request->params['admin'];
	}
}