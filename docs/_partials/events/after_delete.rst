Crud.afterDelete
^^^^^^^^^^^^^^^^

Emitted after ``Table::delete()`` has been called.

The :ref:`Crud Subject <crud-subject>` contains two keys:

- **success** if ``true`` the ``delete()`` call succeeded ``false`` otherwise
- **id** The ID that was originally passed to the action and are usually the primary key of your model.
- **item** The record that was found in the database.
