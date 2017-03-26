************
Crud Subject
************
.. _crud-subject:

The Crud Subject is the class which is passed as the subject of all the events that the Crud plugin emits during its
execution. Depending on the action being scaffolded, and what it's working on the contents of the subject can be
different.

Core event subjects
===================

You can find many of the subject contents are included as part of the :ref:`Core Crud Events <core-crud-events>`
documentation. This is because the subject of the event is very specific to the event being emitted.

When dealing with listeners, you are able to manipulate the subject of the event in order to change Crud's behavior. Such
as changing pagination, or adding extra conditions to a query.

This is an example of the data passed in a ``beforeFind`` event subject.

.. code-block:: phpinline

    <?php
    public function view($id)
    {
        $this->Crud->on('beforeFind', function (\Cake\Event\Event $event) {
            $query = $event->getSubject()->query;
            $primaryKey = $event->getSubject()->id;
            $table = $event->getSubject()->repository;
        });
    }

Find more examples in the :ref:`Core Crud Events <core-crud-events>` documentation, for the event you need.
