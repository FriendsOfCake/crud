JSON API
========

Listener for building a Crud API following the
`JSON API specification <http://jsonapi.org/>`_.

Introduction
------------
This listener brings you an implementation of the JSON API specification version
v1.0 with support for data fetching, data posting, (validation) errors and a ton of
configurable options to manipulate the generated json allowing you to benefit of instant
compatibility with JSON API supporting tools and frameworks like Ember Data.

Please note that some parts of the JSON API specification have not been implemented yet.
Feel free to submit a PR for missing functionality and help work towards a full-featured
implementation of the specification, the effort should be minimal.

Requirements
------------

This listener depends on the `neomerx/json-api <https://github.com/neomerx/json-api>`_
composer package which can be installed by running:

.. code-block:: bash

   composer require neomerx/json-api:^0.8.10

Setup
-----

Routing
^^^^^^^

Only controllers explicitly mapped can be exposed as API resources so make sure
to configure your global routing scope in ``config/router.php`` similar to:

.. code-block:: phpinline

  const API_RESOURCES = [
    'Countries',
    'Currencies',
  ];

  Router::scope('/', function ($routes) {
    foreach (API_RESOURCES as $apiResource) {
        $routes->resources($apiResource, [
            'inflect' => 'dasherize'
        ]);
    }
  });

Controller
^^^^^^^^^^

Attach the listener using the components array if you want to attach
it to all controllers, application wide and make sure ``RequestHandler``
is loaded **before** ``Crud``.

.. code-block:: php

  <?php
  class AppController extends Controller {

    public function initialize()
    {
      $this->loadComponent('RequestHandler');
      $this->loadComponent('Crud.Crud', [
        'actions' => [
          'Crud.Index',
          'Crud.View'
        ],
        'listeners' => ['Crud.JsonApi']
      ]);
    }

Alternatively, attach the listener to your controllers ``beforeFilter``
if you prefer attaching the listener to specific controllers on the fly.

.. code-block:: php

  <?php
  class SamplesController extends AppController {

    public function beforeFilter(\Cake\Event\Event $event) {
      parent::beforeFilter();
      $this->Crud->addListener('Crud.JsonApi');
    }
  }

Request detector
----------------

The JsonApi Listener adds the ``jsonapi`` request detector
to your ``Request`` object which checks if the request
contains a ``HTTP Accept`` header set to ``application/vnd.api+json``
and can be used like this inside your application:

.. code-block:: php

  if ($this->request->is('jsonapi')) {
    return('cool, using JSON API');
  }

.. note::

To make sure the listener won't get in your way it will
return ``NULL`` for all requests unless ``is('jsonapi')`` is true.

Exception handler
-----------------

The JsonApi listener overrides the ``Exception.renderer`` for ``jsonapi`` requests,
so in case of an error, a standardized error will be returned, in either
``json`` or ``xml`` - according to the API request type.

Create a custom exception renderer by extending the Crud's ``JsonApiExceptionRenderer``
class and enabling it with the ``exceptionRenderer`` configuration option.

.. code-block:: php

  <?php
  class AppController extends Controller {

    public function initialize()
    {
      parent::initialize();
      $this->Crud->config(['listeners.api.exceptionRenderer' => 'App\Error\JsonApiExceptionRenderer']);
    }
  }

**Note:** However if you are using CakePHP 3.3+'s PSR7 middleware feature the ``exceptionRenderer``
config won't be used and instead you will have to set the ``Error.exceptionRenderer``
config in ``config/app.php`` to ``'Crud\Error\JsonApiExceptionRenderer'`` as following:

.. code-block:: php

    'Error' => [
        'errorLevel' => E_ALL,
        'exceptionRenderer' => 'Crud\Error\JsonApiExceptionRenderer',
        'skipLog' => [],
        'log' => true,
        'trace' => true,
    ],

Errors/exceptions
^^^^^^^^^^^^^^^^^

For standard errors (e.g. 404) and exceptions the listener will
produce error responses in the following JSON API format:

.. code-block:: json

  {
    "errors": [
      {
        "code": "501",
        "title": "Not Implemented"
      }
    ],
    "debug": {
      "class": "Cake\\Network\\Exception\\NotImplementedException",
      "trace": []
    }
  }

.. note::

Please note that the ``debug`` node with the stack trace will only be included if ``debug`` is true.

Validation errors
^^^^^^^^^^^^^^^^^

For (422) validation errors the listener produces will produce
validation error responses in the following JSON API format.

.. code-block:: json

  {
    "errors": [
      {
        "title": "_required",
        "detail": "Primary data does not contain member 'type'",
        "source": {
          "pointer": "/data"
        }
      }
    ]
  }

.. note::

Please note that the listener also responds with (422) validation errors
when data is posted in a document structure that does not comply with the
JSON API specification.

Response formats
----------------

HTTP GET (index)
^^^^^^^^^^^^^^^^

Requests to the ``index`` action **must** use:

- the ``HTTP GET`` request type
- an ``Accept`` header  set to ``application/vnd.api+json``

A successful request will respond with HTTP response code ``200``
and response body similar to this output produced by
``http://example.com/countries``:

.. code-block:: json

  {
    "data": [
      {
        "type": "countries",
        "id": "1",
        "attributes": {
          "code": "NL",
          "name": "The Netherlands"
        },
        "links": {
          "self": "/countries/1"
        }
      },
      {
        "type": "countries",
        "id": "2",
        "attributes": {
          "code": "BE",
          "name": "Belgium"
        },
        "links": {
          "self": "/countries/2"
        }
      }
    ]
  }

HTTP GET (view)
---------------

Requests to the ``view`` action **must** use:

- the ``HTTP GET`` request type
- an ``Accept`` header  set to ``application/vnd.api+json``

A successful request will respond with HTTP response code ``200``
and response body similar to this output produced by
````http://example.com/countries/1``:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "id": "1",
      "attributes": {
        "code": "NL",
        "name": "The Netherlands"
      },
      "links": {
        "self": "/countries/1"
      }
    }
  }

HTTP POST (add)
---------------

Requests to the ``add`` action **must** use:

- the ``HTTP POST`` request type
- an ``Accept`` header  set to ``application/vnd.api+json``
- a ``Content-Type`` header  set to ``application/vnd.api+json``
- request data in valid JSON API document format

A successful request will respond with HTTP response code ``200``
and response body containing the ``id`` of the newly created
record. Request failing ORM validation will result in a (422) validation
error response as described earlier.

The response body will look similar to this output produced by
``http://example.com/countries``:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "id": "28",
      "attributes": {
        "code": "DK",
        "name": "Denmark"
      },
      "relationships": {
        "currency": {
          "data": {
            "type": "currencies",
            "id": "1"
          },
          "links": {
            "self": "/currencies/1"
          }
        }
      },
      "links": {
        "self": "/countries/10"
      }
    }

JSON API document
^^^^^^^^^^^^^^^^^

All data posted to the listener is transformed from JSON API format to
standard CakePHP format so it can be processed "as usual" once the data
is accepted. To make sure posted data complies with the JSON API
specification it is validated by the listener's DocumentValidator which
will throw a (422) ValidationException if it does not comply along
with a pointer to the cause.

A valid JSON API document structure for creating a new Country
would look similar to:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "attributes": {
        "code": "NL",
        "name": "The Netherlands"
      },
      "relationships": {
        "currency": {
          "data": {
            "type": "currencies",
            "id": "1"
          }
        }
      }
    }
  }

HTTP PATCH (edit)
-----------------

All requests to the ``edit`` action **must** use:

- the ``HTTP PATCH`` request type
- an ``Accept`` header  set to ``application/vnd.api+json``
- a ``Content-Type`` header  set to ``application/vnd.api+json``
- request data in valid JSON API document format
- request data containing the ``id`` of the resource to update

A successful request will respond with HTTP response code ``200``
and response body similar to the one produced by the ``view`` action.

A valid JSON API document structure for updating the ``name`` field
for a Country with ``id`` 10 would look similar to the following output
produced by ``http://example.com/countries/1``:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "id": "10",
      "attributes": {
        "name": "My new name"
      }
    }
  }

HTTP DELETE (delete)
--------------------

All requests to the ``delete`` action **must** use:

- the ``HTTP DELETE`` request type
- an ``Accept`` header  set to ``application/vnd.api+json``
- a ``Content-Type`` header  set to ``application/vnd.api+json``
- request data in valid JSON API document format
- request data containing the ``id`` of the resource to delete

A successful request will return HTTP response code ``204`` (No Content)
and empty response body. Failed requests will return HTTP response
code ``400`` with empty response body.

An valid JSON API document structure for deleting a Country
with ``id`` 10 could look similar to:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "id": "10"
      }
    }
  }

Associated data
---------------

The listener will detect associated data as produced by
``contain`` and will automatically render those associations
into the JSON API response as specified by the specification.

Let's take the following example code for the ``view`` action of
a Country model with a ``belongsTo`` association to Currencies
and a ``hasMany`` relationship with Cultures:

.. code-block:: php

  public function view()
  {
    $this->Crud->on('beforeFind', function (Event $event) {
      $event->getSubject()->query->contain([
        'Currencies',
        'Cultures',
      ]);
    });

    return $this->Crud->execute();
  }

Assuming a successful find the listener would produce the
following JSON API response including all associated data:

.. code-block:: json

  {
    "data": {
      "type": "countries",
      "id": "2",
      "attributes": {
        "code": "BE",
        "name": "Belgium"
      },
      "relationships": {
        "currency": {
          "data": {
            "type": "currencies",
            "id": "1"
          },
          "links": {
            "self": "/currencies/1"
          }
        },
        "cultures": {
          "data": [
            {
              "type": "cultures",
              "id": "2"
            },
            {
              "type": "cultures",
              "id": "3"
            }
          ],
          "links": {
            "self": "/cultures?country_id=2"
          }
        }
      },
      "links": {
        "self": "/countries/2"
      }
    },
    "included": [
      {
        "type": "currencies",
        "id": "1",
        "attributes": {
          "code": "EUR",
          "name": "Euro"
        },
        "links": {
          "self": "/currencies/1"
        }
      },
      {
        "type": "cultures",
        "id": "2",
        "attributes": {
          "code": "nl-BE",
          "name": "Dutch (Belgium)"
        },
        "links": {
          "self": "/cultures/2"
        }
      },
      {
        "type": "cultures",
        "id": "3",
        "attributes": {
          "code": "fr-BE",
          "name": "French (Belgium)"
        },
        "links": {
          "self": "/cultures/3"
        }
      }
    ]
  }

The listener also supports the ``include`` parameter to allow clients to
customize related resources. Using that same example as above, the client
might request ``/countries/2?include=cultures,currencies`` to achieve the
same response. If the include parameter is provided, then only requested
relationships will be included in the ``included`` schema.

It is possible blacklist, or whitelist what the client is allowed to include.
This is done using the listener configuration:

.. code-block:: php

  public function view()
  {
    $this->Crud
      ->listener('jsonApi')
      ->setConfig('queryParameters.include.whitelist', ['cultures', 'cities']);

    return $this->Crud->execute();
  }

Whitelisting will prevent all non-whitelisted associations from being
contained. Blacklisting will prevent any blacklisted associations from
being included. Blacklisting takes precedence of whitelisting (i.e
blacklisting and whitelisting the same association will prevent it from
being included). If you wish to prevent any associations, set the ``blacklist``
config option to ``true``:

.. code-block:: php

  public function view()
  {
    $this->Crud
      ->listener('jsonApi')
      ->setConfig('queryParameters.include.blacklist', true);

    return $this->Crud->execute();
  }

.. note::

Please note that only support for ``belongsTo`` and ``hasMany``
relationships has been implemented.

Configuration
-------------

The output produced by the listener is highly configurable using the Crud
configuration options described in this section. Configure the options
on the fly per action or enable them for all actions in your controller
by adding them to the ``initialize()`` event like this:

.. code-block:: phpinline

  public function initialize()
  {
    parent::initialize();
    $this->Crud->config('listeners.jsonApi.withJsonApiVersion', true);
  }

withJsonApiVersion
^^^^^^^^^^^^^^^^^^

Pass this **mixed** option a boolean with value true (default: false) to
make the listener add the top-level ``jsonapi`` node with member node
``version`` to each response like shown below.

.. code-block:: json

  {
    "jsonapi": {
      "version": "1.0"
    }
  }

Passing an array or hash will achieve the same result but will also generate
the additional `meta` child node.

.. code-block:: json

  {
    "jsonapi": {
      "version": "1.0",
      "meta": {
        "cool": "stuff"
      }
    }
  }

meta
^^^^

Pass this **array** option (default: empty) an array or hash will make the listener
add the the top-level ``jsonapi`` node with member node ``meta`` to each response
like shown below.

.. code-block:: json

  {
    "jsonapi": {
      "meta": {
        "copyright": {
          "name": "FriendsOfCake"
        }
      }
    }
  }

absoluteLinks
^^^^^^^^^^^^^

Setting this **boolean** option to true (default: false) will make the listener
generate absolute links for the JSON API responses.

debugPrettyPrint
^^^^^^^^^^^^^^^^

Setting this **boolean** option to false (default: true) will make the listener
render non-pretty json in debug mode.

jsonOptions
^^^^^^^^^^^

Pass this **array** option (default: empty) an array with
`PHP Predefined JSON Constants <http://php.net/manual/en/json.constants.php>`_
to manipulate the generated json response. For example:

.. code-block:: phpinline

  public function initialize()
  {
    parent::initialize();
    $this->Crud->config('listeners.jsonApi.jsonOptions', [
      JSON_HEX_QUOT,
      JSON_UNESCAPED_UNICODE,
    ]);
  }

include
^^^^^^^

Pass this **array** option (default: empty) an array with associated entity
names to limit the data added to the json ``included`` node.

Please note that entity names:

- must be lowercased
- must be singular for entities with a belongsTo relationship
- must be plural for entities with a hasMany relationship

.. code-block:: phpinline

  $this->Crud->config('listeners.jsonApi.include', [
    'currency', // belongsTo relationship and thus singular
    'cultures' // hasMany relationship and thus plural
  ]);

.. note::

The value of the ``include`` configuration will be overwritten if the
the client uses the ``?include`` query parameter.

fieldSets
^^^^^^^^^

Pass this **array** option (default: empty) a hash with
field names to limit the attributes/fields shown in the
generated json. For example:

.. code-block:: phpinline

  $this->Crud->config('listeners.jsonApi.fieldSets', [
    'countries' => [ // main record
      'name'
    ],
    'currencies' => [ // associated data
      'code'
    ]
  ]);

.. note::

Please note that there is no need to hide ``id`` fields as this
is handled by the listener automatically as per the
`JSON API specification <http://jsonapi.org/format/#document-resource-object-fields>`_.

docValidatorAboutLinks
^^^^^^^^^^^^^^^^^^^^^^

Setting this **boolean** option to true (default: false) will make the listener
add an ``about`` link pointing to an explanation for all validation errors caused
by posting request data in a format that does not comply with the JSON API document
structure.

This option is mainly intended to help developers understand what's wrong with their
posted data structure. An example of an about link for a validation error caused
by a missing ``type`` node in the posted data would be:

.. code-block:: json

  {
    "errors": [
      {
        "links": {
          "about": "http://jsonapi.org/format/#crud-creating"
        },
        "title": "_required",
        "detail": "Primary data does not contain member 'type'",
        "source": {
          "pointer": "/data"
        }
      }
    ]
  }

queryParameters
^^^^^^^^^^^^^^^

This **array** option allows you to specify query parameters to parse in your application.
Currently this listener supports the official ``include`` parameter. You can easily add your own
by specifying a callable.

.. code-block:: phpinline

  $this->Crud->listener('jsonApi')->config('queryParameter.parent', [
    'callable' => function ($queryData, $subject) {
      $subject->query->where('parent' => $queryData);
    }
  ]);

Pagination
----------

This listener fully supports the ``API Pagination`` listener and will,
once enabled as `described here <https://crud.readthedocs.io/en/latest/listeners/api-pagination.html#setup>`_
, add the ``meta`` and ``links`` nodes as per the JSON API specification.

.. code-block:: json

  {
    "meta": {
      "record_count": 15,
      "page_count": 2,
      "page_limit": null
    },
    "links": {
      "self": "/countries?page=2",
      "first": "/countries?page=1",
      "last": "/countries?page=2",
      "prev": "/countries?page=1",
      "next": null
    }
  }

Query Logs
----------

This listener fully supports the ``API Query Log`` listener and will,
once enabled as `described here <https://crud.readthedocs.io/en/latest/listeners/api-query-log.html#setup>`_
, add a top-level ``query`` node to every response when debug mode is enabled.

Schemas
-------

This listener makes use of `NeoMerx schemas <https://github.com/neomerx/json-api/wiki/Schemas>`_
to handle the heavy lifting that is required for converting CakePHP entities to JSON API format.

By default all entities in the ``_entities`` viewVar will be passed to the
Listener's ``DynamicEntitySchema`` for conversion. This dynamic schema extends
``Neomerx\JsonApi\Schema\SchemaProvider`` and is, amongst other things, used to
override NeoMerx methods so we can generate CakePHP specific output (like links).

Even though the dynamic entity schema provided by Crud should cater to the
needs of most users, creating your own custom schemas is also supported. When
using custom schemas please note that the listener will use the first matching
schema, following this order:

1. Custom entity schema
2. Custom dynamic schema
3. Crud's dynamic schema

Custom entity schema
^^^^^^^^^^^^^^^^^^^^

Use a custom entity schema in situations where you need to alter the
generated JSON API but only for a specific controller/entity.

An example would be overriding the NeoMerx ``getSelfSubUrl`` method used
to prefix all ``self`` links in the generated json for a ``Countries``
controller. This would require creating a ``src/Schema/JsonApi/CountrySchema.php``
file looking similar to:

.. code-block:: phpinline

  <?php
  namespace App\Schema\JsonApi;

  use Crud\Schema\JsonApi\DynamicEntitySchema;

  class CountrySchema extends DynamicEntitySchema
  {
    public function getSelfSubUrl($entity = null)
    {
      return 'http://prefix.only/countries/controller/self-links/';
    }
  }

Custom dynamic schema
^^^^^^^^^^^^^^^^^^^^^

Use a custom dynamic schema if you need to alter the generated JSON API for all
controllers, application wide.

An example of a custom dynamic schema would require creating
a ``src/Schema/JsonApi/DynamicEntitySchema.php`` file looking similar to:

.. code-block:: phpinline

  <?php
  namespace App\Schema\JsonApi;

  use Crud\Schema\JsonApi\DynamicEntitySchema as CrudDynamicEntitySchema;

  class DynamicEntitySchema extends CrudDynamicEntitySchema
  {
    public function getSelfSubUrl($entity = null)
    {
      return 'http://prefix.all/controller/self-links/';
    }
  }
