serialize
^^^^^^^^^

.. note::

	This setting is only relevant if you use the :doc:`API listener</listeners/api>`.

.. note::

	The :doc:`API listener</listeners/api>` will always enforce ``success`` and ``data`` to be part of the ``_serialize``
	array.

This method is intended to allow you to add additional keys to your API responses with ease. An example of this is the
:doc:`API Query Log</listeners/api-query-log>`.

To get the current configured ``serialize`` keys call the ``serialize`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->serialize();

To change the serialize keys, pass a ``string`` or an ``array`` as first argument.

If a string is passed, it will be cast to ``array`` automatically.

.. code-block:: phpinline

	$this->Crud->action()->serialize(['my', 'extra', 'keys']);

