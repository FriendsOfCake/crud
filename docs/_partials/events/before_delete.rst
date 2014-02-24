Crud.beforeDelete
^^^^^^^^^^^^^^^^^

Event emitted before calling ``Table::delete``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **id** 			The ID of the entity, from the URL
- **item**	 	The ``Entity`` from the ``find()`` call.

To abort a ``delete()`` simply stop the event by calling
``$event->stopPropagation()``
