Crud.afterPaginate
^^^^^^^^^^^^^^^^^^

This event is emitted right after the call to ``Controller::paginate()``.

The ``items`` property of the event object contains all the database records found in the pagination call.

Modify the Result
"""""""""""""""""

.. code-block:: phpinline

  public function index() {
    $this->Crud->on('afterPaginate', function(\Cake\Event\Event $event) {
      foreach ($event->subject->items as $item) {
        // $item is an entity
      }
    });

    return $this->Crud->execute();
  }
