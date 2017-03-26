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

Defaults are stored in the ``messages`` configuration array for each :doc:`action </actions>`.

If you do not want to use this feature, simply stop the event by calling it's ``stopPropagation()`` method.

If you'd like to customise the flash messages that are used, perhaps you're using
`friendsofcake/bootstrap-ui <https://github.com/friendsofcake/bootstrap-ui>`_. It's actually quite simple to do, and can
be done as part of the component configuration or on the fly.

.. code-block:: phpinline

  public function initialize()
  {
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'edit' => [
                    'className' => 'Crud.Edit',
                    'messages' => [
                        'success' => [
                            'params' => ['class' => 'alert alert-success alert-dismissible']
                        ],
                        'error' => [
                            'params' => ['class' => 'alert alert-danger alert-dismissible']
                        ]
                    ],
                ]
            ]
        ]);
  }

If you'd like to configure it on the fly you can use the eventManager to change the event subject as the event is emitted.

.. code-block:: phpinline

  $this->eventManager()->on('Crud.setFlash', function (Event $event) {
      if ($event->getSubject()->success) {
          $event->getSubject()->params['class'] = 'alert alert-success alert-dismissible';
      }
  });