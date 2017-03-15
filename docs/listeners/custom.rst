Custom
======

Any class can be used as a Crud Listener, even the controller.

Using a controller as a listener
--------------------------------

We override the ``implementedEvents()`` method in the controller, and bind
the ``Crud.beforeFind`` event to the ``_beforeFind()`` method in the controller.

.. code-block:: phpinline

  <?php
  namespace App\Controller;

  class BlogsController extends AppController {

    public function implementedEvents()
    {
        return parent::implementedEvents() + [
            'Crud.beforeFind' => '_beforeFind'
        ];
    }

    public function _beforeFind(\Cake\Event\Event $event, \Cake\ORM\Query $query)
    {

    }

  }

Creating a listener class
-------------------------

Creating your own listener class is very similar to using a controller as a listener.

.. code-block:: phpinline

  <?php
  namespace App\Lib\Listeners;

  use Cake\Event\Event;
  use Crud\Listener\BaseListener;

  class MyListener extends BaseListener
  {
      public function implementedEvents()
      {
          return [
              'Crud.beforeFind' => '_beforeFind'
          ];
      }

      public function _beforeFind(Event $event)
      {
          Log::debug('Inside the listener!');
      }
  }
