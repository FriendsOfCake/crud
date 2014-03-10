Crud.beforePaginate
^^^^^^^^^^^^^^^^^^^

Triggered before ``Controller::paginate()`` is called.

Add conditions
--------------

.. code-block:: phpinline

	public function index() {
		$this->Crud->on('beforePaginate', function(\Cake\Event\Event $event) {
			$this->paginate['conditions']['is_active'] = true;
		});

		return $this->Crud->execute();
	}
