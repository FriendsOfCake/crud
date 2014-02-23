Crud.beforeFind
^^^^^^^^^^^^^^^

The event is triggered just before executing a ``SELECT ... WHERE`` query to the data layer.

The :ref:`crud-subject` contains two objects that allows you to modify the query in the callback

- **repository** An instance of the ``Repository`` (``Table``) the query will be executed against
- **query** A ``Query`` object from the ``Repository`` where ``$PrimaryKey => $IdFromRequest`` is already added to the conditions

.. note::

	**An example**

	Given the URL: ``/posts/view/10`` the ``repository`` object will be an instance of ``PostsTable`` and the ``query``
	includes a ``WHERE`` condition with ``Posts.id = 10``
