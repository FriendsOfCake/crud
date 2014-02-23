Crud.afterSave
^^^^^^^^^^^^^^

.. note::

	Do not confuse this event with the ``afterSave`` callback in the ORM layer

This event is triggered right after the call to ``Table::save()``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **id** The newly inserted ID. It's only available if the call to ``Table::save()`` was successful.
- **success** indicates whether or not the ``Table::save()`` call succeed or not.
- **created** ``true`` if the record was ``created`` and ``false`` if the record was ``updated``.
- **item** An ``entity`` object marshaled with the ``HTTP POST`` data from the request and the ``save()`` logic.
