findMethod
^^^^^^^^^^

The 1st parameter to ``Table::find()`` - the default value is ``all``.

To get the current configured ``findMethod`` keys call the ``findMethod`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->findMethod();

To change the findMethod value pass a `string` argument to the method

.. code-block:: phpinline

	$this->Crud->action()->findMethod('my_custom_finder');
