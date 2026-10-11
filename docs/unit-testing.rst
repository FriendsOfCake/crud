************
Unit Testing
************

To ease with unit testing of Crud Listeners and Crud Actions, it's recommended
to use the proxy methods found in [CrudBaseObject]({{site.url}}/api/develop/class-CrudBaseObject.html).

These methods are much easier to mock than the full `CrudComponent` object.

They also allow you to just mock the methods you need for your specific test, rather than the big dependency nightmare the
CrudComponent can be in some cases.

Proxy methods
=============

These methods are available in all `CrudAction` and `CrudListener` objects.

crud()
-------

Get the CrudComponent instance

.. code-block:: phpinline

	$this->crud()

action($name)
--------------

Get an CrudAction object by it's action name

.. code-block:: phpinline

	$this->action()
	$this->action($name)

trigger($eventName, $data = [])
--------------------------------

Trigger a :doc:`Crud Event<events>`

.. code-block:: phpinline

	$this->trigger('beforeSave')
	$this->trigger('beforeSave', ['data' => 'keys'])
	$this->trigger('beforeSave', $this->subject(['data' => 'keys']))

listener($name)
----------------

Get a :doc:`Listener<listeners>` by its name

.. code-block:: phpinline

	$this->listener('Api')

subject($additional = [])
--------------------------

Create a Crud Subject - used in ``$this->trigger``

.. code-block:: phpinline

	$this->subject()
	$this->subject(['data' => 'keys'])

session()
----------

Get the Session Component instance

.. code-block:: phpinline

	$this->session()

controller()
-------------

Get the controller for the current request

.. code-block:: phpinline

	$this->controller()

request()
----------

Get the current ``Cake\Http\ServerRequest`` for this HTTP Request

.. code-block:: phpinline

	$this->request()

response()
-----------

Get the current ``Cake\Http\Response`` for this HTTP Request

.. code-block:: phpinline

	$this->response()

entity()
---------

Get the entity instance that is created from ``Controller::$modelClass``

.. code-block:: phpinline

	$this->entity()

model()
-------

Get the table instance for the current request

.. code-block:: phpinline

	$this->model()
