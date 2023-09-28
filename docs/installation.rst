************
Installation
************

Using composer
==============

The recommended installation method for this plugin is by using composer.

.. code-block:: sh

	composer require friendsofcake/crud

You can also check `Packagist <https://packagist.org/packages/friendsofcake/crud>`_.

Loading the plugin
==================

Run the following command

.. code-block:: sh

	bin/cake plugin load Crud

Depending on your CakePHP version your ``src/Application.php`` or ``config/bootstrap.php``
will be modified to load the plugin.

Configuring the controller
==========================

The Crud plugin provides a trait which will catch a ``MissingActionException`` and
then step in to provide scaffold actions to the controllers.

To enable Crud across your whole application add the trait to your ``src/Controller/AppController.php``

.. code-block:: php

    namespace App\Controller;

    class AppController extends \Cake\Controller\Controller
    {
        use \Crud\Controller\ControllerTrait;
    }

.. note::

    To have Crud just scaffold a single controller you can just add the ``ControllerTrait`` to that specific controller.

Adding the ``ControllerTrait`` itself do not enable anything Crud, but simply installs the code to handle
the ``\Cake\Controller\Exception\MissingActionException`` exception so you don't have to implement an action in your controller
for Crud to work.

The next step is to load the Crud component in your controller. A basic example is as follows, and will enable the Crud
plugin to scaffold all your controllers index actions.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {
      use \Crud\Controller\ControllerTrait;

      public function initialize(): void
      {
          parent::initialize();

          $this->loadComponent('Crud.Crud', [
              'actions' => [
                  'Crud.Index'
              ]
          ]);

          // Other application wide controller setup
      }
  }

Further configuration options are detailed on the :doc:`configuration page</configuration>`.
