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

# Configuration

The listener do not have any configuration options

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
