view
^^^^

The view file to render.

If the value is ``NULL`` the normal CakePHP behavior will be used

To get the current configured ``view``

.. code-block:: phpinline

	$this->Crud->action()->view();

To change the view to render, pass a ``string`` as first argument

.. code-block:: phpinline

	$this->Crud->action()->view('my_custom_view');
