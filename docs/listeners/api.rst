API
===

This listener allows you to easily create a JSON or XML Api built on top of Crud.

Setup
-----

Routing
^^^^^^^

You need to tell the ``Router`` to `parse extensions <https://book.cakephp.org/5/en/development/routing.html#routing-file-extensions>`_ else it won't be able to process and render ``json`` and ``xml``
URL extension.

Controller
^^^^^^^^^^

Attach it on the fly in your controllers ``beforeFilter``, this is recommended if you want to attach it only to
specific controllers and actions.

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
      parent::beforeFilter($event);
      $this->Crud->addListener('Crud.Api');
    }
  }

Attach it using components array, this is recommended if you want to attach it to all controllers, application wide.

.. code-block:: php

  <?php
  class AppController extends Controller {

    public function initialize(): void
    {
        $this->loadComponent('Crud.Crud', [
          'actions' => [
            'Crud.Index',
            'Crud.View'
          ],
          'listeners' => ['Crud.Api']
        ]);
  }

Request detectors
-----------------

The Api Listener will add a new `api` detector to your ``ServerRequest`` class.

ServerRequest::is('api')
^^^^^^^^^

Checking if the request is either ``is('json')`` or ``is('xml')``.

Default behavior
----------------

If the current request doesn't evaluate ``is('api')`` to true, the listener
won't do anything at all.

All its callbacks will simply return ``NULL`` and don't get in your way.

Exception renderer
------------------

In case of an error, in order to get a standardized response in either
``json`` or ``xml`` - according to the API request type, for `api` requests,
the default CakePHP exception renderer needs to be overridden.

Set the ``exceptionRenderer`` config for the ``ErrorHandlerMiddleware`` in your
``Application::middleware()`` method in ``src/Application.php``.

.. code-block:: php

  $middlewareQueue->add(
    new ErrorHandlerMiddleware(['exceptionRenderer' => \Crud\Error\ExceptionRenderer::class] + Configure::read('Error'), $this)
  )

You also need to update your ``ErrorController`` to use the ``JsonView`` or ``XmlView``
classes for content negotiation.

.. code-block:: php

  // src/Controller/ErrorController.php

  use Crud\View\JsonView;
  use Crud\View\XmlView;

  public function initialize(): void
  {
      $this->addViewClasses([
        'json' => JsonView::class,
        'xml' => XmlView::class,
      ]);
  }

Request type enforcing
----------------------

The API listener will try to enforce some best practices on how an API
should behave.

For a request to ``index`` and ``view`` the HTTP request type **must** be
``HTTP GET`` - else an ``MethodNotAllowed`` exception will be raised.

For a request to ``add`` the HTTP request type **must** be ``HTTP POST`` -
else an ``MethodNotAllowed`` exception will be raised.

For a request to ``edit`` the HTTP request type **must** be ``HTTP PUT`` -
else an ``MethodNotAllowed`` exception will be raised.

For a request to ``delete`` the HTTP request type **must** be ``HTTP DELETE`` -
else an ``MethodNotAllowed`` exception will be raised.

You can `find out more about RESTful on Wikipedia <https://en.wikipedia.org/wiki/Representational_state_transfer#Applied_to_web_services>`_.

Response format
---------------

The default response format for both XML and JSON has two root keys, ``success`` and ``data``. It's possible to add
your own root keys by using the ``serialize`` view builder option.

JSON response
^^^^^^^^^^^^^

.. code-block:: json

  {
    "success": true,
    "data": {

    }
  }


XML response
^^^^^^^^^^^^

.. code-block:: xml

  <response>
    <success>1</success>
    <data></data>
  </response>


Exception response format
-------------------------

The ``data.exception`` key is only returned if ``debug`` is > 0

JSON exception
^^^^^^^^^^^^^^

.. code-block:: json

  {
    "success": false,
    "data": {
      "code": 500,
      "url": "/some/url.json",
      "name": "Some exception message",
      "exception": {
        "class": "CakeException",
        "code": 500,
        "message": "Some exception message",
        "trace": []
      }
    }
  }


XML exception
^^^^^^^^^^^^^

.. code-block:: xml

  <response>
    <success>0</success>
    <data>
      <code>500</code>
      <url>/some/url.json</url>
      <name>Some exception message</name>
      <exception>
        <class>CakeException</class>
        <code>500</code>
        <message>Some exception message</message>
        <trace></trace>
        <trace></trace>
      </exception>
      <queryLog/>
    </data>
  </response>


HTTP POST (add)
---------------

``success`` is based on the ``event->subject->success`` parameter from the
``Add`` action.

If ``success`` is ``false`` a HTTP response code of ``422`` will be returned,
along with a list of validation errors from the model in the ``data`` property
of the response body.

If ``success`` is ``true`` a HTTP response code of ``201`` will be returned,
along with the id of the created record in the ``data`` property of the
response body.

The ``success`` return data can be customized by setting the ``api.success.data.entity`` config for the action.

.. code-block:: phpinline

  //In your Controller/Action
  $this->Crud->action()->setConfig('api.success.data.entity', [
      'id', //Extract the `id` value from the entity and place it into the `id` key in the return data.
      'status' => 'status_value' //Extract the `status_value` value from the entity and place it into the `status` key in the return data.
  ]);


HTTP PUT (edit)
---------------

``success`` is based on the ``event->subject->success`` parameter from the
``Edit`` action.

If ``success`` is ``false`` a HTTP response code of ``422`` will be returned,
along with a list of validation errors from the model in the ``data`` property
of the response body.

If ``success`` is ``true`` a HTTP response code of ``200`` will be returned
(even when the resource has not been updated).

HTTP DELETE (delete)
--------------------

``success`` is based on the ``event->subject->success`` parameter from
the ``Delete`` action.

If ``success`` is ``false`` a HTTP response code of ``400`` will be returned.

If ``success`` is ``true`` a HTTP response code of ``200`` will be returned,
along with empty ``data`` property in the response body.

Not Found (view / edit / delete)
--------------------------------

In case an ``id`` is provided to a crud action and the id does not exist in
the database, a ``404`` NotFoundException` will be thrown.

Invalid id (view / edit / delete)
---------------------------------

In case a ``ìd`` is provided to a crud action and the id is not valid
according to the database type a ``500 BadRequestException`` will be thrown
