Crud.afterDelete
^^^^^^^^^^^^^^^^

This event is emitted after ``Table::delete()`` has been called.

The :ref:`Crud Subject <crud-subject>` contains two keys:

- **success** if ``true`` the ``delete()`` call succeeded, ``false`` otherwise
- **id** The ID that was originally passed to the action and is usually the primary key of your model.
- **item** The record that was found in the database.

Check Success
"""""""""""""

.. code-block:: phpinline

  public function delete($id)
  {
      $this->Crud->on('afterDelete', function(\Cake\Event\Event $event) {
          if (!$event->getSubject()->success) {
              $this->log("Delete failed for entity " . $event->getSubject()->id);
          }
      });

      return $this->Crud->execute();
  }
