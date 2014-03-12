Crud.recordNotFound
^^^^^^^^^^^^^^^^^^^

.. note::

	This event will throw an exception.

	The default configuration will thrown an ``Cake\Error\NotFoundException`` which will yield a 404 response.

The event is triggered after a ``find`` did not find any records in the database.

You can modify the exception class thrown using ``CrudComponent::message`` method

