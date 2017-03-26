Crud.beforeDelete
^^^^^^^^^^^^^^^^^

This event is emitted before calling ``Table::delete``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **id** The ID of the entity, from the URL
- **item** The ``Entity`` from the ``find()`` call.

To abort a ``delete()`` simply stop the event by calling
``$event->stopPropagation()``.

Stop Delete
"""""""""""

.. code-block:: phpinline

  public function delete($id)
  {
      $this->Crud->on('beforeDelete', function(\Cake\Event\Event $event) {
          // Stop the delete event, the entity will not be deleted
          if ($event->getSubject()->item->author !== 'admin') {
              $event->stopPropagation();
          }
      });

      return $this->Crud->execute();
  }
