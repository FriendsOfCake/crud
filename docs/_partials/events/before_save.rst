Crud.beforeSave
^^^^^^^^^^^^^^^

.. note::

	Do not confuse this event with the ``beforeSave`` callback in the ORM layer

Called right before calling ``Table::save()``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **entity** An ``entity`` object marshaled with the ``HTTP POST`` data from the request.
- **saveMethod** A ``string`` with the ``saveMethod``.
- **saveOptions** An ``array`` with the ``saveOptions``.

All modifications to these keys will be passed into the ``Table::$saveMethod``.

.. warning::

	After this event has been emitted, changes done through the ``$action->saveMethod()`` or ``$action->saveOptions()``
	methods will no longer affect the code, as the rest of the code uses the values from the :ref:`Crud Subject <crud-subject>`

To abort a save simply stop the event by calling ``$event->stopPropagation()``.
The ``saveMethod`` will not be called, an error flash message is displayed and
the user is redirected to the ``index`` action.

If your listener has handled the save itself, set ``success`` to ``true`` on the
subject before stopping the event. The success flow (``Crud.afterSave``, a
success flash message and the regular redirect) will run instead.

Stop Save
"""""""""

.. code-block:: phpinline

  $this->Crud->on('beforeSave', function (\Cake\Event\EventInterface $event) {
      // Stop the save event, the entity will not be saved
      if ($event->getSubject()->entity->author !== 'admin') {
          $event->stopPropagation();
      }

      // Stop the save event, but let Crud run the success flow
      // $event->getSubject()->success = true;
      // $event->stopPropagation();
  });
