Crud.afterLookup
^^^^^^^^^^^^^^^^

This event is emitted right after the call to ``Controller::paginate()`` in the Lookup Action.

The ``entities`` property of the event object contains all the database records found in the pagination call.

Modify the Result
"""""""""""""""""

.. code-block:: phpinline

  public function lookup()
  {
      $this->Crud->on('afterLookup', function(\Cake\Event\Event $event) {
          foreach ($event->getSubject()->entities as $entity) {
              // $entity is an entity
          }
      });

      return $this->Crud->execute();
  }
