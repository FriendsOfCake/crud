Redirect listener
=================

Enable more complex redirect rules.

Setup
-----

Attach it on the fly in your controller beforeFilter, this is recommended if
you want to attach it only to specific controllers and actions:

.. code-block:: php

  <?php
  class SamplesController extends AppController {

      public function beforeFilter(\Cake\Event\Event $event) {
          $this->Crud->addListener('Crud.Redirect');

          parent::beforeFilter($event);
      }
  }


Attach it using components array, this is recommended if you want to
attach it to all controllers, application wide:

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function initialize()
    {
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index',
                'view'
            ],
            'listeners' => [
                'Crud.Redirect'
            ]
        ]);
    }
  }

Configuration
-------------

Readers
^^^^^^^

A `reader` is a `closure <http://php.net/closure>`_ that can access a field in an object through different means.

Below is a list of the build-in readers you can use:

================== =================================== =======================================================================
Name                Pseudo code                         Description
================== =================================== =======================================================================
``request.key``     ``$this->request->{$field}``        Access a property directly on the Request object
------------------ ----------------------------------- -----------------------------------------------------------------------
``request.data``    ``$this->request->data($field)``    Access a HTTP POST data field using ``Hash::get()`` compatible format
------------------ ----------------------------------- -----------------------------------------------------------------------
``request.query``   ``$this->request->query($field)``   Access a HTTP query argument using ``Hash::get()`` compatible format
------------------ ----------------------------------- -----------------------------------------------------------------------
``model.key``       ``$Model->{$field}``                Access a property directly on the Model instance
------------------ ----------------------------------- -----------------------------------------------------------------------
``model.data``      ``$Model->data[$field]``            Access a model data key using ``Hash::get()`` compatible format
------------------ ----------------------------------- -----------------------------------------------------------------------
``model.field``     ``$Model->field($field)``           Access a model key by going to the database and read the value
------------------ ----------------------------------- -----------------------------------------------------------------------
``subject.key``     ``$CrudSubject->{$key}``            Access a property directly on the event subject
================== =================================== =======================================================================

Adding your own reader
^^^^^^^^^^^^^^^^^^^^^^

Adding or overriding a reader is very simple.

The closure takes two arguments:

1) ``CrudSubject $subject``

2) ``$key = null``

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
      $listener = $this->Crud->listener('Redirect');
      $listener->reader($name, Closure $closure);

      // Example on a reader using Configure
      $listener->reader('configure.key', function(CrudSubject $subject, $key) {
        return Configure::read($key);
      });

      parent::beforeFilter();
    }
  }
  ?>

Action defaults
^^^^^^^^^^^^^^^

Below is the defaults provided by build-in Crud actions:

Add action
^^^^^^^^^^

By default Add Crud Action always redirect to ``array('action' => 'index')`` on ``afterSave``

============== ================== =========== ==================================== =================================================================================================================================
Name            Reader             Key         Result                               Description
============== ================== =========== ==================================== =================================================================================================================================
``post_add``    ``request.data``   ``_add``    ``array('action' => 'add')``         By providing ``_add`` as a post key, the user will be redirected back to the ``add`` action
-------------- ------------------ ----------- ------------------------------------ ---------------------------------------------------------------------------------------------------------------------------------
``post_edit``   ``request.data``   ``_edit``   ``array('action' => 'edit', $id)``   By providing ``_edit`` as a post key, the user will be redirected to the ``edit`` action with the newly created ID as parameter
============== ================== =========== ==================================== =================================================================================================================================

Edit action
^^^^^^^^^^^

By default Edit Crud Action always redirect to ``array('action' => 'index')`` on ``afterSave``

============== ================== =========== ==================================== ===========================================================================================================================================
Name            Reader             Key         Result                               Description
============== ================== =========== ==================================== ===========================================================================================================================================
``post_add``    ``request.data``   ``_add``    ``array('action' => 'add')``         By providing ``_add`` as a post key, the user will be redirected back to the ``add`` action
-------------- ------------------ ----------- ------------------------------------ -------------------------------------------------------------------------------------------------------------------------------------------
``post_edit``   ``request.data``   ``_edit``   ``array('action' => 'edit', $id)``   By providing ``_edit`` as a post key, the user will be redirected to the ``edit`` action with the same ID as parameter as the current URL
============== ================== =========== ==================================== ===========================================================================================================================================

Configuring your own redirect rules
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

It's very simple to modify existing or add your own redirect rules:

.. code-block:: php

  <?php
  class SamplesController extends AppController
  {

    public function beforeFilter(\Cake\Event\Event $event)
    {
      // Get all the redirect rules
      $rules = $this->Crud->action()->redirectConfig();

      // Get one named rule only
      $rule = $this->Crud->action()->redirectConfig('add');

      // Configure a redirect rule:
      //
      // if $_POST['_view'] is set then redirect to
      // 'view' action with the value of '$subject->id'
      $this->Crud->action()->redirectConfig('view',
          [
              'reader' => 'request.data',    // Any reader from the list above
              'key' => '_view',              // The key to check for, passed to the reader
              'url' => [                     // The url to redirect to
                  'action' => 'view',        // The final url will be '/view/$id'
                  ['subject.key', 'id']      // If an array is encountered, it will be expanded the same was as 'reader'+'key'
              ]
          ]
      );

      parent::beforeFilter($event);
    }
  }
