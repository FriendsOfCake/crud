*********
Listeners
*********

.. tip::

	While CRUD provides many listeners, it's recommended that you add your own reusable
	listeners for your application needs

Listeners are the foundation for the extreme flexibility Crud provides you as an application developer.

The event system allows you to hook into the most important part of the :doc:`Crud actions<actions>` flow and customize
it to your unique application needs.

The Anatomy Of A Listener
=========================

The listener system is simply the
`Events System <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_ from
CakePHP, and all the official documentation and usage also applies to Crud.

The Crud event system uses two methods ``trigger()`` and ``on()`` to interface
the underlying CakePHP event system.

The only hard requirement for a Crud listener is that it needs to either implement
the ``implementedEvents()`` method or extend ``\Crud\Listener\Base``.

Below is the code for a simple Crud listener. In the next few sections we will walk through the code and explain how
it works, and what every single line of code does.

For each section, the relevant lines of code will be highlighted.

Class And Namespace
-------------------

All built-in listeners in Crud live in the ``Crud\Listener`` namespace.

All listeners in Crud, including yours, should inherit from the ``Crud\Listener\Base`` class.
This class is ``abstract`` and provides numerous auxiliary methods which can be useful for you both as a developer and
as an action creator.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 2-4

Implemented Events
------------------

As documented in the `CakePHP Events System <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_
all listeners must contain a ``implementedEvents`` method.

In this example, we simply request that ``beforeRender`` in our class is executed
every time a ``Crud.beforeRender`` event is emitted.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 6-18

.. note::

	The ``Crud.beforeRender`` event is similar to the Controller and View event of the
	same name, but ``Crud.beforeRender`` is called first, and can halt the entire
	rendering process

The Callback
------------

This method gets executed every time a ``Crud.beforeRender`` event is emitted from within Crud or by you as a
developer. When the event is emitted, we append a header to the client HTTP response named ``X-Powered-By`` with
the value ``CRUD 4.0``.

.. literalinclude:: _code/listener_example.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 20-30

Included listeners
==================

Crud comes with a selection of listeners covering the most common use-cases. These allow you to tap into the events
within the plugin and change behavior to suit your application, or to provide extra functionality, such as an API.

.. toctree::
	:maxdepth: 1

	listeners/api
	listeners/api-pagination
	listeners/api-query-log
	listeners/jsonapi
	listeners/redirect
	listeners/related-models
	listeners/search
	Create your own <listeners/custom>
