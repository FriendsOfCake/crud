Api Query Log
=============

.. note::

  This feature requires the :doc:`API listener<api>` to work.

This listener appends query log information to the API responses

The listener will only append the ``queryLog`` key if ``debug`` is 2 or greater

Setup
-----

Attach it on the fly in your controller beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter() {
      $this->Crud->addListener('Crud.Api'); // Required
      $this->Crud->addListener('Crud.ApiQueryLog');
    }

  }

Attach it using components array, this is recommended if you want to
attach it to all controllers, application wide

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public $components = [
      'RequestHandler',
      'Crud.Crud' => [
        'listeners' => [
          'Crud.Api', // Required
          'Crud.ApiQueryLog'
        ]
      ];

  }


Output
------

Paginated results will include a

.. code-block:: json

  {
    "success": true,
    "data": [

    ],
    "queryLog": {
      "default": {
        "log": [
          {
            "query": "SELECT SOMETHING FROM SOMEWHERE",
            "took": 2,
            "params": [

            ],
            "affected": 25,
            "numRows": 25
          },
          {
            "query": "SELECT SOMETHING FROM SOMEWHERE'",
            "params": [

            ],
            "affected": 1,
            "numRows": 1,
            "took": 0
          }
        ]
      }
    }
  }
