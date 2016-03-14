Delete
======

The ``Delete Crud Action`` will delete a record by the id provided in the URL.

Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/find_method.rst
.. include:: /_partials/actions/configuration/serialize.rst

Events
------

This is a list of events emitted from the ``Delete Crud Action``.

Please see the :doc:`events documentation</events>` for a full list of generic
properties and how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_find.rst
.. include:: /_partials/events/after_find.rst
.. include:: /_partials/events/record_not_found.rst
.. include:: /_partials/events/before_delete.rst
.. include:: /_partials/events/after_delete.rst
.. include:: /_partials/events/before_redirect.rst
