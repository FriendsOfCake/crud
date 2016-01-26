Crud.beforeRedirect
^^^^^^^^^^^^^^^^^^^

Simple and event driven wrapper for ``Controller::redirect()``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **url** 		The 1st argument to ``Controller::redirect()``.
- **status** 	The 2nd argument to ``Controller::redirect()``.
- **exit** 		The 3rd argument to ``Controller::redirect()``.
- **entity**	(Optional) The ``Entity`` from the previously emitted event.

All keys can be modified as you see fit, at the end of the event cycle they will be passed
directly to ``Controller::redirect()``.

The redirect ``$url`` can be changed on the fly either by posting a ``redirect_url`` field from your
form or by providing a ``redirect_url`` HTTP query key.

The default for most redirects are simply to return to the ``index()`` action.
