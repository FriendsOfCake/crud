view
^^^^

Get or set the view file to render at the end of the request.

The view setting is passed directly and unmodified to ``Controller::render()``.

To get the current configured ``view`` call the ``view`` method without any arguments.

.. code-block:: phpinline

	$this->Crud->action()->view();

To change the view to render, pass a ``string`` as first argument.

.. code-block:: phpinline

	$this->Crud->action()->view('my_custom_view');

.. note::

	If the first parameter is ``NULL`` - which is the default value - the normal CakePHP behavior will be used.

.. warning::

	Due to the nature of this method, once a custom view has been set, it's not possible to revert back to
	the default behavior by calling ``->view(null)`` as it will return the current configuration.
