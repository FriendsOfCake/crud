Crud.beforePaginate
^^^^^^^^^^^^^^^^^^^

This event is emitted before ``Controller::paginate()`` is called.

Add Conditions
""""""""""""""

.. code-block:: phpinline

  public function index()
  {
      $this->Crud->on('beforePaginate', function(\Cake\Event\EventInterface $event) {
          $this->paginate['conditions']['is_active'] = true;
      });

      return $this->Crud->execute();
  }
