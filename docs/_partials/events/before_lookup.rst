Crud.beforeLookup
^^^^^^^^^^^^^^^^^

This event is emitted before ``Controller::paginate()`` is called inside the Lookup Action.

Add Conditions
""""""""""""""

.. code-block:: phpinline

  public function lookup()
  {
      $this->Crud->on('beforeLookup', function(\Cake\Event\Event $event) {
          $this->paginate['conditions']['is_active'] = true;
      });

      return $this->Crud->execute();
  }
