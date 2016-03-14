Lookup
======

The ``Lookup Crud Action`` will display a record from a data source for auto-complete purposes. Used mostly for `Crud-View <https://github.com/friendsofcake/crud-view>`_.

Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/view.rst
.. include:: /_partials/actions/configuration/view_var.rst
.. include:: /_partials/actions/configuration/serialize.rst

Events
------

This is a list of events emitted from the ``Lookup Crud Action``.

Please see the :doc:`events documentation</events>` for a full list of generic
properties and how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_lookup.rst
.. include:: /_partials/events/after_lookup.rst
.. include:: /_partials/events/record_not_found.rst
.. include:: /_partials/events/before_render.rst
