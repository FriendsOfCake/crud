Edit
====

The ``Edit Crud Action`` will update an existing record if the request is ``POST`` or ``PUT``
and the data validates - otherwise it will attempt to render a form to the end-user.

Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/view.rst
.. include:: /_partials/actions/configuration/find_method.rst
.. include:: /_partials/actions/configuration/save_options.rst
.. include:: /_partials/actions/configuration/serialize.rst
.. include:: /_partials/actions/configuration/related_models.rst

Events
------

This is a list of events emitted from the ``Edit Crud Action``.

Please see the :doc:`events documentation</events>` for a full list of generic properties and
how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_find.rst
.. include:: /_partials/events/after_find.rst
.. include:: /_partials/events/before_save.rst
.. include:: /_partials/events/after_save.rst
.. include:: /_partials/events/set_flash.rst
.. include:: /_partials/events/before_redirect.rst
.. include:: /_partials/events/before_render.rst
