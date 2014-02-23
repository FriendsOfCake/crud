Crud.afterPaginate
^^^^^^^^^^^^^^^^^^

This event is triggered right after the call to ``Controller::paginate()``.

The ``items`` property of event object contains all the database record found in the pagination call.
