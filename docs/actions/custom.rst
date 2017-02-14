Custom Action Classes
=====================

If you need to customize an action for any reason, you can create your own custom Crud action class.

A Crud Action can respond to any ``HTTP`` verb (``GET``, ``POST``, ``PUT``, ``DELETE``).
Each HTTP verb can be implemented as method, e.g. ``_get()`` for HTTP ``GET``,
``_post()`` for HTTP ``POST`` and ``_put()`` for HTTP ``PUT``.

If no HTTP verb specific method is found in the class, ``_handle()`` will be executed.

A default custom index action might be as simple as the following:

.. code-block:: phpinline

    <?php
    namespace App\Crud\Action;

    class MyIndexAction extends \Crud\Action\BaseAction
    {
        /**
         * Default settings
         *
         * @var array
         */
        protected $_defaultConfig = [
            'enabled' => true,
            'scope' => 'table',
            'findMethod' => 'all',
            'view' => null,
            'viewVar' => null,
            'serialize' => [],
            'api' => [
                'success' => [
                    'code' => 200
                ],
                'error' => [
                    'code' => 400
                ]
            ]
        ];
    
        /**
        * Generic handler for all HTTP verbs
        *
        * @return void
        */
        protected function _handle()
        {
            $query = $this->_table()->find($this->findMethod());
            $items = $this->_controller()->paginate($query);
        }

    }

.. note::

  In this basic example, we have removed all the events that are emitted.

Why
---

The most common use-cases for a custom action class is when you need to have specific code run on all your controllers
for a certain action. For example reading from the session or adjusting the query to add dynamic complex conditions.

Remember that in the :doc:`/configuration` you can configure your action classes on a per-action basis, so you might just
want a custom action for a single action across your controllers.

Using your custom action class
------------------------------

Once you have created your custom action class, you can configure Crud to use it for specific actions by changing the
Crud component configuration.

.. code-block:: phpinline

  class AppController extends \Cake\Controller\Controller
  {

      use \Crud\Controller\ControllerTrait;

      public function initialize()
      {
          parent::initialize();

          $this->loadComponent('Crud.Crud', [
              'actions' => [
                  'index' => ['className' => '\App\Crud\Action\MyIndexAction']
              ]
          ]);
      }

.. note::

  Ensure that you escape your namespace when loading your own action classes.
