Related Models
==============

If you are used to bake or CakePHP scaffolding you might want to have some
control over the data it is sent to the view for filling select boxes for
associated models.

Introduction
------------

CRUD can be configured to return the list of record for all
related models or just those you want to in a per-action basis.

By default all related model lists for main CRUD table instance
will be fetched, but only for add, edit and corresponding admin actions.

For instance if your ``Posts`` table in associated to ``Tags`` and ``Authors``,
then for the aforementioned actions you will have in your view the ``$authors``
and ``$tags`` variable containing the result of calling ``find(‘list’)`` on
each table.

Should you need more fine grain control over the lists fetched, you can
configure statically or use dynamic methods.

Configuring
-----------

Before you're able to configure your ``relatedModels`` you need to load the listener.

.. code-block:: php

  <?php
  class AppController extends Controller {

    public function initialize()
    {
      parent::initialize();
      $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
    }

.. code-block:: php

You can enable and disable which model relations you want to have automatically
fetched very easily, as shown below.

If you set ``relatedModels`` to ``true`` all model relations will be fetched
automatically.

If you set ``relatedModels`` to an ``array``, only the related models in that
array will be fetched automatically.

If you set ``relatedModels`` to ``false`` no model relations will be fetched
automatically.

.. code-block:: php

	<?php
	class DemoController extends AppController {

		public $components = [
			'Crud.Crud' => [
				'actions' => [
					'add' => ['relatedModels' => ['Author']],
					'edit' => ['relatedModels' => ['Tag', 'Cms.Page']]
				]
			]
		];

	}

It’s possible to dynamically reconfigure the relatedModels listener

.. code-block:: php

	<?php
	// This can be changed in beforeFilter and the controller action
	public function beforeFilter(\Cake\Event\Event $event) {
		// Automatically executes find('list') on the User ($users) and Tag ($tags) tables
		$this->Crud->listener('relatedModels')->relatedModels(['User', 'Tag'], 'your_action');

		// Automatically executes find('list') on the User ($users) table
		$this->Crud->listener('relatedModels')->relatedModels(['User'], 'your_action');

		// Fetch related data from all table relations (default)
		$this->Crud->listener('relatedModels')->relatedModels(true);

		// Don't fetch any related data
		$this->Crud->listener('relatedModels')->relatedModels(false);

		// Get the current configuration
		$config = $this->Crud->listener('relatedModels')->relatedModels();
	}

Events
------

If for any reason you need to alter the query or final results generated
by fetching related models lists, you can use the ``Crud.relatedModel`` event
to inject your own logic.

``Crud.relatedModel`` will receive the following parameters in the event
subject, which can be altered on the fly before any result is fetched

* ``name`` The name of the relation
* ``viewVar`` The name of the variable when set to the view
* ``query`` The ``\Cake\ORM\Query`` object used for the ``find('list')``
* ``association`` The ``\Cake\ORM\Association`` object

Example

.. code-block:: php

	<?php
	class DemoController extends AppController {

		public function beforeFilter(\Cake\Event\Event $event) {
			parent::beforeFilter();

			$this->Crud->on('relatedModel', function(\Cake\Event\Event $event) {
				if ($event->subject->association->name() === 'Authors') {
					$event->subject->query->limit(3);
					$event->subject->query->where(['is_active' => true]);
				}
			});

		}

	}
