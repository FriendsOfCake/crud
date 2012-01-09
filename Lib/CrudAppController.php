<?php
App::uses('CrudCollection', 'Crud.Lib');

abstract class CrudAppController extends Controller {
    public $formCallbacks = array(
        'index' => array(

        ),
        'add' => array(

        ),
        'edit' => array(

        ),
        'view' => array(

        ),
        'common' => array(
            'Crud.Default' => array()
        ),
        'delete' => array(

        )
    );

    public function index() {
        $CallbackCollection = $this->_getCallbackCollection(__FUNCTION__);
        $CallbackCollection->trigger('beforePaginate', array($this));
        $this->set('items', $this->paginate());
    }

    /**
     * Default add action
     *
     * @abstract Overwrite in controller as needed
     * @param uuid $id
     */
    public function add() {
        $CallbackCollection = $this->_getCallbackCollection(__FUNCTION__);

        if ($this->request->is('post') || $this->request->is('put')) {
            $CallbackCollection->trigger('beforeSave');
            if ($this->{$this->modelClass}->saveAll($this->request->data, array('validate' => 'first', 'atomic' => true))) {
                $CallbackCollection->trigger('afterSave', array(true, $this->{$this->modelClass}->id));

                $this->Session->setFlash(__d('common', 'Could not create %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/success');
                $this->redirect(array('action' => 'index'));
            } else {
                $CallbackCollection->trigger('afterSave', array(false));
                $this->Session->setFlash(__d('common', 'Could not create %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
            }
        }

        $CallbackCollection->trigger('beforeRender');
        if ($this->autoRender) {
            return $this->render('form');
        }
    }

    /**
     * Default edit action
     *
     * @abstract Overwrite in controller as needed
     * @param uuid $id
     */
    public function edit($id = null) {
        $this->validateUUID($id);
        $CallbackCollection = $this->_getCallbackCollection(__FUNCTION__);

        if ($this->request->is('post') || $this->request->is('put')) {
            $CallbackCollection->trigger('beforeSave');
            if ($this->{$this->modelClass}->saveAll($this->data, array('validate' => 'first', 'atomic' => true))) {
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

            $this->data = $this->{$this->modelClass}->find('first', $query);
            if (empty($this->data)) {
                $CallbackCollection->trigger('recordNotFound', array($id));
                $this->Session->setFlash(__d('common', 'Could not find %s', Inflector::humanize($this->{$this->modelClass}->name)), 'flash/error');
                $this->redirect(array('action' => 'index'));
            }
            $this->data = $CallbackCollection->trigger('afterFind', array($this->data));
        }

        $CallbackCollection->trigger('beforeRender');
        if ($this->autoRender) {
            return $this->render('form');
        }
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
    public function view($id = null) {
        // Validate ID parameter
        $this->validateUUID($id);

        // Initialize callback collection
        $CallbackCollection = $this->_getCallbackCollection(__FUNCTION__);

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
            return $this->render('view');
        }
    }

    /**
    * Generic delete action
    *
    * Triggers the following callbacks
    *  -
    */
    public function delete($id = null) {
        $this->validateUUID($id);
        $CallbackCollection = $this->_getCallbackCollection(__FUNCTION__);

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
}