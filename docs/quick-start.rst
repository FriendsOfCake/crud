***********
Quick Start
***********

You are busy, and you just want to "get things done™", so let's get going.

After :doc:`installation<installation>`, you are ready to CRUD-ify your app.

The application
===============

So the application our `pointy-haired boss <https://www.google.com/search?q=pointy+haired+boss>`_ has tasked us to create today is a Blog.

App Controller
==============

Let's setup CRUD to handle all ``index()``, ``add()``, ``edit()``, ``view()`` and ``delete()`` actions automatically,
we do this by enabling Crud in the ``AppController`` with the ``actions`` options configuration.

.. code-block:: php

    namespace App\Controller;

    class AppController extends \Cake\Controller\Controller
    {
        use \Crud\Controller\ControllerTrait;

        public function initialize()
        {
            parent::initialize();

            $this->loadComponent('Crud.Crud', [
                'actions' => [
                    'Crud.Index',
                    'Crud.Add',
                    'Crud.Edit',
                    'Crud.View',
                    'Crud.Delete'
                ]
            ]);
        }
    }

There we go, that was easy.

Posts Controller
================

So, our new Blog needs a Posts controller to allow us to create, read, update and delete posts.

.. code-block:: php

  namespace App\Controller;

  class PostsController extends AppController
  {
  }

This is all the code we need in the ``PostsController`` as Crud will scaffold the controller actions for us.

If you are not using `Crud-View <https://github.com/FriendsOfCake/crud-view>`_ then you will have
to `bake your templates <http://book.cakephp.org/3.0/en/bake/usage.html>`_.

.. code-block:: sh

  bin/cake bake template posts

Let's check out our new application, go to ``/posts`` and behold, a nice paginated ``ìndex()`` template, all without any code
in your controller.

You should now be able to navigate to ``/posts/add`` as well and create your first post, as well as being able to edit it.

Reference application
=====================

If you'd rather look through a completed application, José from the CakePHP core team has created an application using Crud.
`You can find it on Github <https://github.com/lorenzo/cakephp3-bookmarkr>`_.