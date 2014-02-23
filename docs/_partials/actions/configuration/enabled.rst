enabled
^^^^^^^

Test or modify if the Crud Action is enabled or not.

When a CrudAction is disabled, Crud will not handle any requests to the action, and CakePHP will raise the normal
``\Cake\Error\MissingActionException`` exception if you haven't implemented the action in your controller.

To test if an action is enabled simply call the method without any arguments

.. code-block:: phpinline

	$this->Crud->action()->enabled();

To disable an action, pass ``false`` as argument to the method

.. code-block:: phpinline

	$this->Crud->action()->enabled(false);

To enable an action, pass ``true`` as argument to the method

.. code-block:: phpinline

	$this->Crud->action()->enabled(true);
