*******
Actions
*******

.. note::

	CRUD already provides the basic ``Index``, ``View``, ``Add``, ``Edit`` and
	``Delete`` actions,	so you do not need to implement these on your own.
	You can find the documentation for these actions in the menu to the left.

Crud Actions are the backbone of the plugin, this is where most of the logic happens.

What Is An Action?
==================

A Crud action roughly translates to a normal Controller action. The primary difference is that CRUD actions are made to
be as generic and secure out of the box as possible.

You can consider a CRUD action as a more flexible PHP trait that fits nicely within the CakePHP ecosystem.

A nice added bonus is that if all your controllers share a generic controller action, you only have to unit test once,
to test every controller in your application.

The Anatomy Of An Action
========================

Below is the code for the :doc:`Index Crud Action<actions/index>`

In the next few sections we will walk through the code and explain how it works,
and what every single line of code does. For each section, the relevant lines of code will be highlighted.

.. literalinclude:: _code/action_index.php
	 :language: php
	 :linenos:


Class And Namespace
-------------------

All build-in actions in Crud live in the ``Crud\Action`` namespace.

All actions in Crud, even your own, should inherit from the
``Crud\Action\Base`` class.
This class is ``abstract`` and provides numerous auxiliary methods which can be
useful for you both as a developer as an action creator.

Where you choose to put your custom Crud actions is up to you, but using ``src/Crud`` is a good place. Remember to
namespace your action class accordingly.

.. literalinclude:: _code/action_index.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 2-4, 25-27

Request Methods
---------------

Next is the method ``_handle``. A Crud Action can respond to any HTTP verb
(``GET``, ``POST``, ``PUT``, ``DELETE``).
Each HTTP verb can be implemented as method, e.g. ``_get()`` for HTTP GET,
``_post()`` for HTTP POST and ``_put()`` for HTTP PUT.

If no HTTP verb specific method is found in the class, ``_handle()`` will be
executed.

.. literalinclude:: _code/action_index.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 6-11,25

You can treat the ``_handle()`` method as a catch-all, if your crud action
wants to process all possible HTTP verbs.

An advantage of this setup is that you can separate the logic on a request type
level instead of mixing all of the logic into one big block of code.

For example the :doc:`Edit Crud Action<actions/edit>` implements ``_get()``,
``_post()`` and ``_put()`` methods. The ``_get()`` method simply reads the entity
from the database and passes it to the form, while ``_put()`` handles validation
and saving the entity back to the database.

Events & Subject
----------------

All Crud actions emit a range of events, and all of these events always contain a Crud Subject. The Crud Subject can
change its state between emitted events. This object is a simple ``StdClass`` which contains the current state of the Crud request.

The real beauty of Crud is the events and the flexibility they provide.

All calls to ``_trigger()`` emit an event, that you as a developer can listen to and inject your own application logic.
These events are in no way magical, they are simply normal
`CakePHP events <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_, dispatched like all
other `events in CakePHP <http://book.cakephp.org/3.0/en/core-libraries/events.html>`_.

You can for example listen for the ``beforePaginate`` event and add conditions to your pagination query, just with a
few lines of code. Those few lines of code is what makes your application unique. The rest of the code you would
normally have is simply repeated boiler plate code.

.. literalinclude:: _code/action_index.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 12-15,19,21,24

Boilerplate
-----------

Only the code that you would normally have in your controller is left now.

While these 3 lines seem simple, and the whole Crud implementation a bit overkill at first, the true power of this setup
will be clear when your application grows and the requirements increase.

.. literalinclude:: _code/action_index.php
	 :language: php
	 :linenos:
	 :emphasize-lines: 17,18,23

For example :doc:`adding an API layer<api>` to your application later in time will be easy because you don't need to edit
all your applications many controllers.

Using Crud, it would be as simple as loading the :doc:`API listener<listeners/api>` and everything would be taken care
of. All validation, exceptions, success and error responses would work immediately, and with just a few lines of code.

This is because the powerful event system can hook into the request and hijack the rendering easily and effortlessly;
something baked controllers do not offer.

Crud's default actions
======================

Crud has many actions built-in which come with the plugin.

.. toctree::
	:maxdepth: 1

	actions/index
	actions/view
	actions/add
	actions/edit
	actions/delete
	actions/lookup
	actions/bulk
	actions/bulk-delete
	actions/bulk-set-value
	actions/bulk-toggle
	Custom <actions/custom>
