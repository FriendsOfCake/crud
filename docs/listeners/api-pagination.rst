API Pagination
==============

.. warning::

	This feature requires the :doc:`API listener<api>` to work.

This listener appends pagination information to the API responses that is contain
pagination information.

Setup
-----
Attach this listener to your AppController components array if you want to make
it available for all your controllers, application wide.

.. code-block:: php

    <?php
    class AppController extends \Cake\Controller\Controller {

        public function initialize()
        {
            $this->loadComponent('RequestHandler');
            $this->loadComponent('Crud.Crud', [
                'listeners' => [
                    'Crud.Api', // Required
                    'Crud.ApiPagination'
                ]
            ]);
        }
    }


Attach it on the fly in your controller beforeFilter if you want to limit
availability of the listener to specific controllers and actions.

.. code-block:: php

    <?php
    class SamplesController extends AppController {

        public function beforeFilter(\Cake\Event\Event $event)
        {
            $this->Crud->addListener('Crud.Api'); // Required
            $this->Crud->addListener('Crud.ApiPagination');
        }

    }

Output
------

Paginated results will include a new `pagination` element similar to the one
below:

.. code-block:: json

	{
		"success": true,
		"data":[

		],
		"pagination":{
			"page_count": 13,
			"current_page": 1,
			"count": 25,
			"has_prev_page": false,
			"has_next_page": true
		}
	}

Configuration
-------------

Configure this listener by setting the
`CakePHP Pagination <http://book.cakephp.org/3.0/en/controllers/components/pagination.html>`_ options directly to the
query object.

.. code-block:: php

    public function index()
    {
        $this->Crud->on('beforePaginate', function (\Cake\Event\Event $event) {
            $event->getSubject()->query->contain([
                'Comments' => function ($q) {
                    return $q
                        ->select(['id', 'name', 'description'])
                        ->where([
                            'Comments.approved' => true
                        ]);
                }
            ]);
        });
    }
