Index
=====

The ``index`` CrudAction paginates over the primary model in the controller.

Events
------

This is a list of events emitted from the ``CrudAction``

In addition to the **subject properties** listed below, there is also a long list of objects
that are always available in all events.

Please see the :doc:`events documentation</events>` for a full list of subject properties and how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/initialize.rst
.. include:: /_partials/events/before_paginate.rst
.. include:: /_partials/events/after_paginate.rst
.. include:: /_partials/events/before_render.rst


Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/find_method.rst
.. include:: /_partials/actions/configuration/view.rst
.. include:: /_partials/actions/configuration/view_var.rst
.. include:: /_partials/actions/configuration/serialize.rst

# Query string parameters

You can easily add query string pagination to your Api `index` actions by adding
Api Pagination and enabling query strings. This will give api-requesters the possibility
to create custom data collections using GET parameters in the URL
(e.g. `http://example.com/controller.{format}?key=value`)

The following query string pagination parameters will automatically become available:

- **limit**: an integer limiting the number of results
- **sort**: the string value of a fieldname to sort the results by
- **direction**: either `asc` or `desc` (only works in combination with the `sort` parameter)
- **page**: an integer pointing to a specific data collection page

[Please also see the CakePHP documentation on Pagination](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html)

[Please also see the CakePHP documentation on out of range `page` requests](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html#out-of-range-page-requests)

Setup by enabling Api Pagination as described [here]({{site.url}}/docs/listeners/api-pagination.html#setup)

Then enable query string pagination by adding this to your `/Controller/AppController.php` file

{% highlight php %}
<?php
  public $paginate = array(
    'paramType' => 'querystring'
  );
?>
{% endhighlight %}
