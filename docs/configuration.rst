*************
Configuration
*************

Configuration of Crud is done through the Crud component - either on the fly
anywhere in you application, or by providing the configuration in the
``Controller::loadComponent()`` method.

Assuming you have followed the :doc:`installation guide<installation>` we will
now begin the actual configuration of Crud.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller {

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud');

  }

At this time, the Crud Component is loaded, but we need to tell Crud which actions we want it to handle for us.

Actions
=======

The list of actions is provided either as Component configuration, or on the fly.

An example configuration for handling index actions looks like this.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {

    use \Crud\Controller\ControllerTrait;

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index'
            ]
        ]);

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
  all ``actions`` provided by Crud you can pick and chose which to use.
  Crud will not force take-over any application logic, and you can enable/disable
  them as you see fit.

Action configuration
====================

.. note::

  Each :doc:`Crud Action<actions>` can have a different set of configuration
  settings, please see their individual documentation pages for more information.

A more verbose example now, where we'll change the view template that Crud will use for index actions to be ``my_index.ctp``

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {

    use \Crud\Controller\ControllerTrait;

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => [
                  'className' => 'Crud.Index',
                  'view' => 'my_index'
                ]
            ]
        ]);

An example of on the fly enabling a Crud action with configuration

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
========================

If you've loaded an action in eg. your ``AppController`` - but don't want it included in a specific controller, it can
be disabled with the ``$this->Crud->disable(['action_name'])``.

Example of disabling a loaded action, first we show all actions being configured to be handled by Crud, then disabling a
specific action in our ``PostsController``.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {

    use \Crud\Controller\ControllerTrait;

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Delete',
                'Crud.Edit'
            ]
        ]);

.. code-block:: phpinline

  class PostsController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
      parent::beforeFilter($event);

      $this->Crud->disable(['Edit', 'Delete']);
    }

  }

Built-in actions
================

Crud provides the default create, read, update and delete actions out of the box.

* :doc:`Index Action<actions/index>`
* :doc:`View Action<actions/view>`
* :doc:`Add Action<actions/add>`
* :doc:`Edit Action<actions/edit>`
* :doc:`Delete Action<actions/delete>`
* :doc:`Lookup Action<actions/lookup>`
* :doc:`Bulk Delete Action<actions/bulk-delete>`
* :doc:`Bulk Set Value Action<actions/bulk-set-value>`
* :doc:`Bulk Field Toggle Action<actions/bulk-toggle>`

Custom action classes
=====================

It's possible to create your own custom action classes as well, or overwrite the built-in ones. Simply provide
the ``className`` configuration key for an action, and Crud will use that one instead.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {

    use \Crud\Controller\ControllerTrait;

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => ['className' => '\\App\\Crud\\MyIndexAction'],
                'view' => ['className' => '\\App\\Crud\\MyViewAction']
            ]
        ]);

.. note::

  Ensure that you escape your namespace when loading your own action classes.

:doc:`Learn more about custom action classes </actions/custom>`.

Listeners
=========

The other way to customise the behavior of the Crud plugin is through it's many listeners. These provide lots of
additional functionality to your scaffolding, such as dealing with api's and loading related data.

Check the :doc:`listeners` documentation for more on Crud's included listeners, and how to create your own.

