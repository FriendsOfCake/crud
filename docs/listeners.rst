Listeners
=========

.. tip::

	While CRUD provides many listeners, it's definitely possible and recommended
	that you add your own reusable listeners for your application needs

Listeners are the foundation for the extreme flexibility CRUD provides you
as an application developer.

The event system allows you to hook into the most important part of the
:doc:`CRUD action<actions>` flow and customize it to your unique application
needs.

The Anatomy Of A Listener
^^^^^^^^^^^^^^^^^^^^^^^^^

The listener system is simply the
`Events System <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_ from
CakePHP, and all the official documentation and usage also applies to CRUD.

The CRUD event system uses two methods ``trigger()`` and ``on()`` to interface
the underlying CakePHP event system.

The only hard requirement for a CRUD listener is that it needs to either implement
the ``implementedEvents()`` method or extend ``\Crud\Listener\Base``.

Below is the code for a simple CRUD listener.

In the next few sections we will walk through the code and explain how it works,
and what every single line of code does.

For each section, the relevant lines of code will be highlighted.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:

Class And Namespace
-------------------

All built-in listeners in CRUD live in the ``Crud\Listener`` namespace.

All listeners in CRUD, even your own, should inherit from the
``Crud\Listener\Base`` class.
This class is ``abstract`` and provides numerous auxiliary methods which can be
useful for you both as a developer and as an action creator.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 2-4, 28

Implemented Events
------------------

As documented in the `CakePHP Events System <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_
all listeners must contain a ``implementedEvents`` method.

In this example, we simply request that ``beforeRender`` in our class is executed
every time a ``Crud.beforeRender`` event is emitted.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 6-16

.. note::

	The ``Crud.beforeRender`` event is similar to the Controller and View event of the
	same name, but ``Crud.beforeRender`` is called first, and can halt the entire
	rendering process

The Callback
------------

This method gets executed every time a ``Crud.beforeRender`` event is emitted
from within CRUD or by you as a developer.

When the event is emitted, we append a header to the client HTTP response named
``X-Powered-By`` with the value ``CRUD 4.0``.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 18-26

More on listeners
^^^^^^^^^^^^^^^^^

.. toctree::

	listeners/api
	listeners/api-pagination
	listeners/api-query-log
	listeners/redirect
	listeners/related-models
	listeners/custom
