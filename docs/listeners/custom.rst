Custom
======

Any class can be used as a CRUD Listener, even the controller.

Example Controller
------------------

We override the ``implementedEvents()`` method in the controller, and bind
the ``Crud.beforeFind`` event to the ``_beforeFind()`` method in the controller.

.. code-block:: php

  <?php
  namespace app\Controller;

  class BlogsController extends AppController {

    public function implementedEvents() {
      return parent::implementedEvents() + [
        'Crud.beforeFind' => '_beforeFind'
      ];
    }

    public function _beforeFind(\Cake\Event\Event $event) {

    }

  }
