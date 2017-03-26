Related Models
==============

If you are used to bake or CakePHP scaffolding you might want to have some control over the data it sends to the view
for filling select boxes for associated models, as well as populating related data in index views.

Introduction
------------

Crud can be configured to return a list of records for all related models or just those you want to on a per-action
basis. By default all related model lists for the main Crud table instance will be fetched, but only for ``add``,
``edit`` and corresponding admin actions.

For instance if your ``Posts`` table is associated with ``Tags`` and ``Authors``, then for those actions
you will have in your view the ``$authors`` and ``$tags`` variables containing the result of calling ``find(‘list’)`` on
each table.

Should you need more fine grain control over the lists fetched, you can configure it statically or use dynamic methods.

For ``index`` methods, you can specify the related models which should be contained during the pagination query.

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

You can enable and disable which model relations you want to have automatically fetched very easily, as shown below.

* If you set ``relatedModels`` to ``true`` all model relations will be fetched automatically.
* If you set ``relatedModels`` to an ``array``, only the related models in that array will be fetched automatically.
* If you set ``relatedModels`` to ``false`` no model relations will be fetched automatically.

.. code-block:: php

    <?php
    class DemoController extends AppController {

        public function initialize()
        {
            $this->loadComponent('Crud.Crud', [
                'actions' => [
                    'index' => [
                        'className' => 'Crud.Index',
                        'relatedModels' => true
                    ],
                    'add' => [
                        'className' => 'Crud.Add',
                        'relatedModels' => ['Authors']
                    ],
                    'edit' => [
                        'className' => 'Crud.Edit',
                        'relatedModels' => ['Tags', 'Cms.Pages']
                    ]
                ]
            ]);
        }
    }

The above example will add a contain to your query for all related models in your ``index`` method, and will perform a
``find('list')`` for related data for your ``add`` and ``edit`` actions.

.. note::

  The Related Models listener performs differently for the ``index`` method, than it does for ``add``
  and ``edit``.

.. note::

  The Related Models listener are aware of custom binding keys for your relations, and will use them
  over the primary key (which is CakePHP default behavior)

If you need to contain extra data in your ``add`` and ``edit`` methods, then you can hook the ``beforeFind`` event and
adjust the queries contain as you need. You can find out how in the :ref:`crud-beforefind`.

It’s possible to dynamically reconfigure the relatedModels listener

.. code-block:: php

    <?php
    // This can be changed in beforeFilter and the controller action

    public function beforeFilter(\Cake\Event\Event $event)
    {
        // Automatically executes find('list') on the Users ($users) and Tags ($tags) tables
        $this->Crud->listener('relatedModels')->relatedModels(['Users', 'Tags'], 'your_action');

        // Automatically executes find('list') on the Users ($users) table
        $this->Crud->listener('relatedModels')->relatedModels(['Users'], 'your_action');

        // Fetch related data from all table relations (default)
        $this->Crud->listener('relatedModels')->relatedModels(true);

        // Don't fetch any related data
        $this->Crud->listener('relatedModels')->relatedModels(false);

        // Get the current configuration
        $config = $this->Crud->listener('relatedModels')->relatedModels();
    }

Events
------

If for any reason you need to alter the ``find('list')`` query or final results generated
by fetching related models lists, you can use the ``Crud.relatedModel`` event
to inject your own logic.

``Crud.relatedModel`` will receive the following parameters in the event
subject, which can be altered on the fly before any result is fetched

* ``name`` The name of the relation
* ``viewVar`` The name of the variable when set to the view
* ``query`` The ``\Cake\ORM\Query`` object used for the ``find('list')``
* ``association`` The ``\Cake\ORM\Association`` object
* ``entity`` The ``Cake\ORM\Entity`` you are finding relations for

Example

.. code-block:: php

    <?php
    class DemoController extends AppController {

        public function beforeFilter(\Cake\Event\Event $event) {
            parent::beforeFilter();

            $this->Crud->on('relatedModel', function(\Cake\Event\Event $event) {
                if ($event->getSubject()->association->name() === 'Authors') {
                    $event->getSubject()->query->limit(3);
                    $event->getSubject()->query->where(['is_active' => true]);
                }
            });

        }
    }
