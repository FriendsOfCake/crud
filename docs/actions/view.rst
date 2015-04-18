View
====

The ``View Crud Action`` will read a record from a data source based on the ID
that is part of the request.

Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/find_method.rst
.. include:: /_partials/actions/configuration/view.rst
.. include:: /_partials/actions/configuration/view_var.rst
.. include:: /_partials/actions/configuration/serialize.rst

Events
------

This is a list of events emitted from the ``View Crud Action``.

Please see the :doc:`events documentation</events>` for a full list of generic
properties and how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_find.rst
.. include:: /_partials/events/after_find.rst
.. include:: /_partials/events/record_not_found.rst
.. include:: /_partials/events/before_render.rst
