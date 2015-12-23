Search 
=======

This listener provides search capabilities for the Crud plugin.

Introduction
^^^^^^^^^^^^
The ``Search listener`` depends on the ``FriendsOfCake`` repo ``search``.

[Please also see the repo]
(https://github.com/FriendsOfCake/search)

Setup
^^^^^

Installation
-------------

You need to install `FriendsOfCake/Search <https://github.com/FriendsOfCake/search>`_ first.

Controller
----------

Attach it on the fly in your controller beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions:

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
      $this->Crud->addListener('Crud.Search');

      parent::beforeFilter();
    }
  }
  ?>


Attach it using components array, this is recommended if you want to
attach it to all controllers, application wide:

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public $components = [
      'Crud.Crud' => [
        'actions' => ['index', 'view'],
        'listeners' => ['Crud.Search']
      ];

  }
  ?>
