Crud.afterSave
^^^^^^^^^^^^^^

.. note::

  Do not confuse this event with the ``afterSave`` callback in the ORM layer.

This event is emitted right after the call to ``Table::save()``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **id** The newly inserted ID. It's only available if the call to ``Table::save()`` was successful.
- **success** indicates whether or not the ``Table::save()`` call succeed or not.
- **created** ``true`` if the record was ``created`` and ``false`` if the record was ``updated``.
- **entity** An ``entity`` object marshaled with the ``HTTP POST`` data from the request and the ``save()`` logic.

Check Created Status
""""""""""""""""""""

.. code-block:: phpinline

  public function edit($id)
  {
      $this->Crud->on('afterSave', function(\Cake\Event\Event $event) {
          if ($event->getSubject()->created) {
              $this->log("The entity was created");
          } else {
              $this->log("The entity was updated");
          }
      });

      return $this->Crud->execute();
  }

Check Success Status
""""""""""""""""""""

.. code-block:: phpinline

  public function edit($id)
  {
      $this->Crud->on('afterSave', function(\Cake\Event\Event $event) {
          if ($event->getSubject()->success) {
              $this->log("The entity was saved successfully");
          } else {
              $this->log("The entity was NOT saved successfully");
          }
      });

      return $this->Crud->execute();
  }

Get Entity ID
"""""""""""""

.. code-block:: phpinline

  public function add()
  {
      $this->Crud->on('afterSave', function(\Cake\Event\Event $event) {
          if ($event->getSubject()->created) {
              $this->log("The entity was created with id: " . $event->getSubject()->id);
          }
      });

      return $this->Crud->execute();
  }
