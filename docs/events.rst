******
Events
******

Events are the backbone of Crud, and your primary gateway into customization of
Crud and fitting it to your applications.

You can subscribe to events from almost everywhere, and in multiple ways.

Controller
==========

implementedEvents
-----------------

We override the ``implementedEvents()`` method in the controller, and bind
the ``Crud.beforeFind`` event to the ``_beforeFind()`` method in the controller.

When using this technique, you need to prefix all the event names with ``Crud.``

Most of the other ways to listen for events do not need this, as it's done
automatically.

.. code-block:: php

  namespace app\Controller;

  class BlogsController extends AppController
  {

      public function implementedEvents()
      {
          return parent::implementedEvents() + ['Crud.beforeFind' => '_beforeFind'];
      }

      public function _beforeFind(\Cake\Event\Event $event)
      {

      }
  }

.. note::

  It's important that the controller event method is ``public``, since it's called
  from the CakePHP event manager, outside of the Controller scope.

  The added ``_`` prefix is there only to prevent it being executed as an controller
  action.

Action
------

You can bind events directly in your controller actions, simply call the ``on()`` method in Crud and provide a
callback. The example below uses a ``closure`` for the callback, but everything that is valid for ``call_user_func()``
can be used

.. code-block:: phpinline

  public function view($id)
  {
    $this->Crud->on('beforeFind', function(\Cake\Event\Event $event) {
        // Will only execute for the view() action
    });

    return $this->Crud->execute();
  }

.. note::

  When implementing events in your controller actions, it's important to
  include ``return $this->Crud->execute();`` otherwise Crud will not process the action.

The benefit of the controller method is that you can easily share it between two actions, like below.

.. code-block:: phpinline

  public function view($id)
  {
      $this->Crud->on('beforeFind', [$this, '_beforeFind']);
      return $this->Crud->execute();
  }

  public function admin_view($id)
  {
      $this->Crud->on('beforeFind', [$this, '_beforeFind']);
      return $this->Crud->execute();
  }

  public function _beforeFind(\Cake\Event\Event $event)
  {
      // Will execute for both view() and admin_view()
  }

.. _core-crud-events:

Core Crud Events
================

Different :doc:`Crud actions <actions>` will emit a different combination of events during their execution, with different
:doc:`Subject <crud-subject>` data. If you are looking for events specific to an action, check the specific
:doc:`Crud action <actions>` documentation page.

Included actions
----------------

This is a full list of all events emitted from Crud.

.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/startup.rst

.. include:: /_partials/events/before_delete.rst
.. include:: /_partials/events/after_delete.rst

.. _crud-beforefind:
.. include:: /_partials/events/before_find.rst
.. include:: /_partials/events/after_find.rst

.. include:: /_partials/events/before_save.rst
.. include:: /_partials/events/after_save.rst

.. include:: /_partials/events/before_paginate.rst
.. include:: /_partials/events/after_paginate.rst

.. include:: /_partials/events/before_redirect.rst

.. include:: /_partials/events/before_render.rst

.. include:: /_partials/events/record_not_found.rst
.. include:: /_partials/events/set_flash.rst