saveMethod
^^^^^^^^^^

The method to execute on ``Table::`` when saving an entity - the default value is ``save``.

To get the current configured ``saveMethod`` keys call the ``saveMethod`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->saveMethod();

To change the saveMethod value pass an `string` argument to the method

.. code-block:: phpinline

	$this->Crud->action()->saveMethod('my_custom_save_method');

saveOptions
^^^^^^^^^^^

The 2nd parameter to ``Table::save()`` - the default value is ``['validate' => true, 'atomic' => true]``.

To get the current configured ``saveOptions`` keys call the ``saveOptions`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->saveOptions();

To change the saveOptions value pass an `array` argument to the method

.. code-block:: phpinline

	$this->Crud->action()->saveOptions(['atomic' => false]);

Sometimes you need to change the accessible fields before you update your entity.

.. code-block:: phpinline

	$this->Crud->action()->saveOptions(['accessibleFields' => ['role_id' => true]]);
