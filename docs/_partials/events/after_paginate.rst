Crud.afterPaginate
^^^^^^^^^^^^^^^^^^

This event is emitted right after the call to ``Controller::paginate()``.

The ``entities`` property of the event object contains all the database records found in the pagination call.

Modify the Result
"""""""""""""""""

.. code-block:: phpinline

  public function index()
  {
      $this->Crud->on('afterPaginate', function(\Cake\Event\Event $event) {
          foreach ($event->getSubject()->entities as $entity) {
              // $entity is an entity
          }
      });

      return $this->Crud->execute();
  }
