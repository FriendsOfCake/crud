Index
=====

The ``Index Crud Action`` paginates over the primary model in the controller.

On a high level it's basically just calling ``Controller::paginate()``.

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

This is a list of events emitted from the ``Index Crud Action``.

Please see the :doc:`events documentation</events>` for a full list of generic properties and
how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_paginate.rst
.. include:: /_partials/events/after_paginate.rst
.. include:: /_partials/events/before_render.rst
