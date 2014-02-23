Crud.beforeFind
^^^^^^^^^^^^^^^

The event is triggered just before executing a ``SELECT ... WHERE`` query to the data layer.

The :ref:`Crud Subject <crud-subject>` contains two objects that allows you to modify the query in the callback, as well
as the ``id`` from the request URL:

- **id** The ID that was originally passed to the action and usually the primary key value of your table.
- **repository** An instance of the ``Repository`` (``Table``) the query will be executed against.
- **query** A ``Query`` object from the ``Repository`` where ``$PrimaryKey => $IdFromRequest`` is already added to the conditions.

This is the last place you can modify the query before it's executed against the database.

.. note::

	**An example**

	Given the URL: ``/posts/view/10`` the ``repository`` object will be an instance of ``PostsTable`` and the ``query``
	includes a ``WHERE`` condition with ``Posts.id = 10``

After the event has emitted, the database query is executed with ``LIMIT 1``.

If a record is found the ``Crud.afterFind`` is emitted.

.. warning::

	If no record is found in the database, the :doc:`Crud.recordNotFound` event is emitted instead of ``Crud.afterFind``.
