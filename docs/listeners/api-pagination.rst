API Pagination
==============

.. note::

	This feature requires the :doc:`API listener<api>` to work.

This listener appends pagination information to the API responses that is contain
pagination information.

Setup
-----

Attach it on the fly in your controller beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions

.. code-block:: php

	<?php
	class SamplesController extends AppController {

		public function beforeFilter(\Cake\Event\Event $event) {
			$this->Crud->addListener('Crud.Api'); // Required
			$this->Crud->addListener('Crud.ApiPagination');
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
					'Crud.ApiPagination'
				]
			];

	}

Output
------

Paginated results will include a

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
