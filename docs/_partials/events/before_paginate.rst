Crud.beforePaginate
^^^^^^^^^^^^^^^^^^^

Triggered before ``Controller::paginate()`` is called.

The ``paginator`` property is a reference to the ``PaginatorComponent``.

If you wish to modify the pagination settings, you should **only** modify ``$event->subject->paginator->settings``.

Modifying ``Controller::$paginate`` will not have any effect during this callback.
