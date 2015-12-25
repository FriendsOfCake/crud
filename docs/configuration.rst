Configuration
=============

Configuration of Crud is done through the Crud ``component`` - either on the fly
anywhere in you application, or by providing the configuration in the
``Controller::$components`` property.

Assuming you have followed the :doc:`installation guide<installation>` we will
now begin the actual configuration of Crud.

Crud is loaded like any other ``Component`` in CakePHP - simply by adding it to
the ``$components`` variable in the controller

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public $components = ['Crud.Crud'];

  }

At this time, the Crud Component is loaded and ready for usage.

However, Crud has not been configured to handle any controller actions yet.

Actions
-------

Configuring Crud to handle actions is simple.

The list of actions is provided either as ``Component`` configuration, or on the
fly.

An example of ``Component`` configuration:

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public $components = [
      'Crud.Crud' => [
        'actions' => ['Crud.Index']
      ]
    ];

  }

An example of on the fly enabling an Crud action:

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public function beforeFilter(\Cake\Event\Event $event) {
      $this->Crud->mapAction('index', 'Crud.Index');
    }

  }

The examples above are functionally identical, and instructs Crud to handle the
``index`` action in controllers using ``Crud.Index`` action class.

.. note::

  If you do not wish for Crud to be enabled across all controllers, or even use
  all ``actions`` provided by Crud
  you can pick and chose which to use. Crud will not force take-over any
  application logic, and you can enable/disable
  them as you see fit.

Action configuration
--------------------

.. note::

  Each :doc:`Crud Action<actions>` have a different set of configuration
  settings, please see their individual documentation page for more information.

Passing in configuration for an action is simple.

.. note::

  In the examples below, we reconfigure the `Index Action` to render
  ``my_index.ctp`` instead of ``index.ctp``

An example of ``Component`` configuration

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public $components = [
      'Crud.Crud' => [
        'actions' => [
          'index' => ['className' => 'Crud.Index', 'view' => 'my_index']
        ]
      ]
    ];

  }

An example of on the fly enabling an Crud action with configuration

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public function beforeFilter(\Cake\Event\Event $event) {
      $this->Crud->mapAction('index', [
        'className' => 'Crud.Index',
        'view' => 'my_index'
      ]);
    }

  }

Disabling loaded actions
------------------------
If you've loaded an action in eg. your ``AppController`` - but don't want it included in a specific controller, it can be disabled with the ``$this->Crud->disable(['action_name'])``.

Example of disable a loaded action:

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public $components = [
      'Crud.Crud' => [
        'actions' => ['Crud.Index', 'Crud.View', 'Crud.Delete', 'Crud.Edit']
      ]
    ];

  }

.. code-block:: phpinline

  class PostsController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
      parent::beforeFilter($event);

      $this->Crud->disable(['Edit', 'Delete']);
    }

  }

Built-in actions
----------------

Crud provides the default ``CRUD`` actions out of the box.

* :doc:`Index Action<actions/index>`
* :doc:`View Action<actions/view>`
* :doc:`Add Action<actions/add>`
* :doc:`Edit Action<actions/edit>`
* :doc:`Delete Action<actions/delete>`
* :doc:`Lookup Action<actions/lookup>`
* :doc:`Bulk Delete Action<actions/bulk-delete>`
* :doc:`Bulk Set Value Action<actions/bulk-set-value>`
* :doc:`Bulk Field Toggle Action<actions/bulk-toggle>`

It's possible to create your own ``Crud Action`` as well, or overwrite the
built-in ones.

Simply provide the ``className`` configuration key for an action, and Crud will
use that one instead.

Listeners
---------

.. note::

  Each :doc:`Crud Listener<listeners>` have a different set of configuration
  settings, please see their individual documentation page for more information.

