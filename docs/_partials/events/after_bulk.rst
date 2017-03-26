Crud.afterBulk
^^^^^^^^^^^^^^

This event is emitted after calling ``_bulk()`` on a Bulk Crud action.

The :ref:`Crud Subject <crud-subject>` contains two keys:

- **success** if ``true`` the ``_bulk()`` call succeeded, ``false`` otherwise
- **ids** A list of ids of entities, from the request data
- **repository** An instance of the ``Repository`` (``Table``) which the query will be executed against.
- **query** A ``Query`` object from the ``Repository`` where ``$PrimaryKey => $IdFromRequest`` is already added to the conditions.

Check Success
"""""""""""""

.. code-block:: phpinline

  public function bulk($id)
  {
      $this->Crud->on('afterBulk', function(\Cake\Event\Event $event) {
          if (!$event->getSubject()->success) {
              $this->log("Bulk action failed");
          }
      });

      return $this->Crud->execute();
  }
