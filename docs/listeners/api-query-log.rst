API Query Log
=============

.. warning::

	This feature requires the :doc:`API listener<api>` to work.

This listener appends query log information to the API responses

.. note::

    The listener will only append the ``queryLog`` key if ``debug`` is set to true.

Setup
-----

Attach it on the fly in your controller beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions

.. code-block:: php

    <?php
    class SamplesController extends AppController
    {
        public function beforeFilter(\Cake\Event\EventInterface $event)
        {
            $this->Crud->addListener('Crud.Api'); // Required
            $this->Crud->addListener('Crud.ApiQueryLog');
        }
    }

Attach it in :code:`AppController::initialize()`, this is recommended if you want to
attach it to all controllers, application wide


.. code-block:: php

    <?php
    class AppController extends \Cake\Controller\Controller
    {
        public function initialize(): void
        {
            $this->loadComponent('Crud.Crud', [
                'listeners' => [
                    'Crud.Api', // Required
                    'Crud.ApiQueryLog'
                ]
            ]);
        }
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


Configuration
-------------

By default this listener will log all defined connections.

If you need to select specific connections to log, you can use the :code:`connections` configuration:

.. code-block:: php

    $this->loadComponent('Crud.Crud', [
        'listeners' => [
            'Crud.Api',
            'ApiQueryLog' => [
                'className' => 'Crud.ApiQueryLog',
                'connections' => ['default', 'elastic']
            ]
        ]
    ]);
