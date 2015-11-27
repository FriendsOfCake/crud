Quick Start
===========

You are busy, and you just want to ``get things done™``, so let's get going.

After :doc:`installation<installation>`, you are ready to CRUD-ify your app.

The application
--------------

So the application our `pointy-haired boss <https://www.google.com/search?q=pointy+haired+boss>`_ has tasked us to create today is a Blog.

App Controller
--------------

Since CRUD is awesome, and you already started to kinda love it, we want to enable CRUD for our entire application.

Let's setup CRUD to handle all ``index()``, ``add()``, ``edit()``, ``view()`` and ``delete()`` actions automatically,
we do this by enabling Crud in the ``AppController`` with the correct ``actions`` configuration.

.. code-block:: php

    <?php
    namespace App\Controller;

    class AppController extends \Cake\Controller\Controller {

      use \Crud\Controller\ControllerTrait;

      public $components = [
        'Crud.Crud' => [
          'actions' => [
            'Crud.Index',
            'Crud.Add',
            'Crud.Edit',
            'Crud.View',
            'Crud.Delete'
          ]
        ]
      ];
    }

There we go, that was easy.

Posts Controller
----------------

So, our new shiny Blog needs a ``Posts Controller`` to, well, manage the posts.

.. code-block:: php

  <?php

  namespace App\Controller;

  class PostsController extends AppController {

  }

(...) and that's it! we don't really need any logic there for now, since we have configured CRUD to take care of all actions

But... since CRUD doesn't provide any views (yet), you will have to bake the views for now

.. code-block:: text

  Console/cake bake template posts

Let's check out our new application, go to ``/posts`` and behold, a nice paginated ``ìndex()`` template, all without any code
in your controller.

You should now be able to navigate to ``/posts/add`` as well and create your first post.

Creating an API
---------------

This section is WIP.
