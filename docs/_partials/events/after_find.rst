Crud.afterFind
^^^^^^^^^^^^^^

After the query has been executed, and a record has been found this event is emitted.

The :ref:`Crud Subject <crud-subject>` contains two keys:

- ``id`` The ID that was originally passed to the action and is usually the primary key of your model.
- ``entity`` The record that was found in the database.

.. note::

  If an entity is not found, the ``RecordNotFound`` event is emitted instead.

Logging the Found Item
""""""""""""""""""""""

.. code-block:: phpinline

  public function delete($id)
  {
      $this->Crud->on('afterFind', function(\Cake\Event\Event $event) {
          $this->log("Found item: " . $event->getSubject()->entity->id . " in the database");
      });

      return $this->Crud->execute();
  }
