viewVar
^^^^^^^

.. note::

	This maps directly to the ``$key`` argument in ``Controller::set($key, $value)``

Change the name of the variable which contains the result of a ``index`` or ``view`` action query result.

To get the current configured ``viewVar`` call the ``viewViar`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->viewVar();

To change the viewVar, pass a ``string`` as first argument.

.. code-block:: phpinline

	$this->Crud->action()->viewVar('items');

For :doc:`Index Action</actions/index>` the default is **plural** version of the controller name.

Having a controller named ``PostsController`` would mean that the ``viewVar`` would be ``posts`` by default.

For :doc:`View Action</actions/view>` the default is **singular** version of the controller name.

Having a controller named ``PostsController`` would mean that the ``viewVar`` would be ``post`` by default.
