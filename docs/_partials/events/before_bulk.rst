Crud.beforeBulk
^^^^^^^^^^^^^^^

This event is emitted before ``_bulk()`` is called on a Bulk Crud action.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **ids** A list of ids of entities, from the request data
- **repository** An instance of the ``Repository`` (``Table``) which the query will be executed against.
- **query** A ``Query`` object from the ``Repository`` where ``$PrimaryKey => $IdFromRequest`` is already added to the conditions.

To abort a bulk action, simply stop the event by calling
``$event->stopPropagation()``.

Stop Bulk Action
""""""""""""""""

.. code-block:: phpinline

  public function bulk($id)
  {
      $this->Crud->on('beforeBulk', function(\Cake\Event\Event $event) {
          // Stop the bulk event, the action will not continue
          if ($event->getSubject()->item->author !== 'admin') {
              $event->stopPropagation();
          }
      });

      return $this->Crud->execute();
  }
