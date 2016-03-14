enabled
^^^^^^^

Test or modify if the Crud Action is enabled or not.

When a CrudAction is disabled, Crud will not handle any requests to the action, and CakePHP will raise the normal
``\Cake\Error\MissingActionException`` exception if you haven't implemented the action in your controller.

.. warning::

    If you have enabled Crud and you are still receiving a ``MissingActionException``, ensure the action is enabled and
    that the controller has the ``\Crud\Controller\ControllerTrait`` implemented.

To test if an action is enabled, call the ``enabled`` method on the action.

.. code-block:: phpinline

	$this->Crud->action()->enabled();

To disable an action, call the ``disable`` method on the action.

.. code-block:: phpinline

	$this->Crud->action()->disable();

To enable an action, call the ``enable`` method on the action.

.. code-block:: phpinline

	$this->Crud->action()->enable();

To disable or enable multiple actions at the same time, ``Crud Component`` provides helper methods.

The ``enable`` and ``disable`` method can take a string or an array, for easy mass-updating.

.. code-block:: phpinline

	$this->Crud->enable('index');
	$this->Crud->enable(['index', 'add']);

	$this->Crud->disable('index');
	$this->Crud->disable(['index', 'add']);

.. note::

	These methods simply calls the ``enable`` and ``disable`` method in each ``Crud Action`` class, and do not provide any magic
	other than mass updating.

.. warning::

	While it's possible to update the ``enabled`` property directly on an action using the ``config`` methods,
	it's not recommend, as important cleanup logic will not be applied if you use the ``config()`` method directly.
