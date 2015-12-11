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
