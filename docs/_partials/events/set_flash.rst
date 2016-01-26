Crud.setFlash
^^^^^^^^^^^^^

Simple and event driven wrapper for ``SessionComponent::setFlash``.

The :ref:`Crud Subject <crud-subject>` contains the following keys:

- **text** 		The 1st argument to ``SessionComponent::setFlash``.
- **element** The 2nd argument to ``SessionComponent::setFlash``.
- **params** 	The 3rd argument to ``SessionComponent::setFlash``.
- **key** 		The 4th argument to ``SessionComponent::setFlash``.
- **entity** 	(Optional) The ``Entity`` from the previously emitted event.

All keys can be modified as you see fit, at the end of the event cycle they will be passed
directly to ``SessionComponent::setFlash``.

Defaults are stored in the ``messages`` configuration array for each action.

If you do not want to use this feature, simply stop the event by calling it's ``stopPropagation()`` method.
