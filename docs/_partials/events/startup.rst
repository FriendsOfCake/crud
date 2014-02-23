Crud.startup
^^^^^^^^^^^^

Called after the ``Controller::beforeFilter()`` and before the Crud action.

It's emitted from ``CrudComponent::startup()`` and thus is fired in the same cycle
as all ``Component::startup()`` events.
