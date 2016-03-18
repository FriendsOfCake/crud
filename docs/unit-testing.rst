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

_crud()
-------

Get the CrudComponent instance

.. code-block:: phpinline

	$this->_crud()

_action($name)
--------------

Get an CrudAction object by it's action name

.. code-block:: phpinline

	$this->_action()
	$this->_action($name)

_trigger($eventName, $data = [])
--------------------------------

Trigger a :doc:`Crud Event<events>`

.. code-block:: phpinline

	$this->_trigger('beforeSave')
	$this->_trigger('beforeSave', ['data' => 'keys'])
	$this->_trigger('beforeSave', $this->_subject(['data' => 'keys']))

_listener($name)
----------------

Get a :doc:`Listener<listeners>` by its name

.. code-block:: phpinline

	$this->_listener('Api')

_subject($additional = [])
--------------------------

Create a Crud Subject - used in ``$this->_trigger``

.. code-block:: phpinline

	$this->_subject()
	$this->_subject(['data' => 'keys'])

_session()
----------

Get the Session Component instance

.. code-block:: phpinline

	$this->_session()

_controller()
-------------

Get the controller for the current request

.. code-block:: phpinline

	$this->_controller()

_request()
----------

Get the current ``Cake\Network\Request`` for this HTTP Request

.. code-block:: phpinline

	$this->_request()

_response()
-----------

Get the current ``Cake\Network\Response`` for this HTTP Request

.. code-block:: phpinline

	$this->_response()

_entity()
---------

Get the entity instance that is created from ``Controller::$modelClass``

.. code-block:: phpinline

	$this->_entity()

_table()
--------

Get the table instance that is created from ``Controller::$modelClass``

.. code-block:: phpinline

	$this->_table()
