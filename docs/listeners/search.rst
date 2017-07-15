Search
======

This listener provides search capabilities for the Crud plugin.

Introduction
------------

The Search listener depends on the `friendsofcake/search <https://packagist.org/packages/friendsofcake/search>`_ package.

Setup
-----

Installation
^^^^^^^^^^^^

.. code-block:: sh

  composer require friendsofcake/search

Controller
^^^^^^^^^^

Attach it on the fly in your controllers beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions.

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
        $this->Crud->addListener('Crud.Search', [
            // Events to listen for and apply search finder to query.
            'enabled' => [
                'Crud.beforeLookup',
                'Crud.beforePaginate'
            ],
            // Search collection to use
            'collection' => 'default'
        ]);

        parent::beforeFilter($event);
    }
  }

Attach it using components array, this is recommended if you want to
attach it to all controllers, application wide.

.. code-block:: php

  <?php
  class DemoController extends AppController {

      public function initialize()
      {
          $this->loadComponent('Crud.Crud', [
              'actions' => [
                  'index'
              ],
              'listeners' => [
                  'Crud.Search'
              ]
          ]);
      }
  }
