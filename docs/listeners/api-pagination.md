---
title: Api Pagination
layout: default
---

# Api Pagination

Adds pagination information to the API responses

# Setup

This feature requires the [Api listener]({{site.url}}/docs/listeners/api.html) to work

## Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    $this->Crud->addListener('Api');
    $this->Crud->addListener('ApiPagination');
  }

}
?>
{% endhighlight %}

## Attach it using components array

This is recommended if you want to attach it to all controllers, application wide

{% highlight php %}
<?php
class SamplesController extends AppController {

  public $components = [
    'RequestHandler',
    'Crud.Crud' => [
      'actions' => ['index', 'view'],
      'listeners' => ['Api', 'ApiPagination']
    ];

}
?>
{% endhighlight %}

## Enable query string parameters (optional)

Api Pagination adds instant query string pagination support to your api which will give
api-requesters the possibility to create custom data collections using GET parameters
in the URL (e.g. `http://example.com/controller.{format}?key=value`)

The following query string pagination parameters are supported

- **limit**: an integer limiting the number of results
- **sort**: the string value of a fieldname to sort the results by
- **direction**: either `asc` or `desc` (works only in combination with the `sort` parameter
- **page**: an integer pointing to a specific data collection page

[Please also see the CakePHP documentation on out of range `page` requests](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html#out-of-range-page-requests)

Enable query string pagination by adding this to your `/Controller/AppController.php` file

{% highlight php %}
<?php
  public $paginate = array(
    'paramType' => 'querystring'
  );
?>
{% endhighlight %}

# Configuration

The listener does not have any configuration options

# Example output

{% highlight json %}
{
   "success": true,
   "data":[

   ],
   "pagination":{
      "page_count": 13,
      "current_page": 1,
      "count": 25,
      "has_prev_page": false,
      "has_next_page": true
   }
}
{% endhighlight %}
