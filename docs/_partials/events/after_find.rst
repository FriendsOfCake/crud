Crud.afterFind
^^^^^^^^^^^^^^

After the query has been executed, and a record has been found this event is emitted.

The :ref:`Crud Subject <crud-subject>` contains two keys:

- ``id`` The ID that was originally passed to the action and are usually the primary key of your model.
- ``item`` The record that was found in the database.
