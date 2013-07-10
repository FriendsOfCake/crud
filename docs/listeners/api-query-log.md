---
title: Api Query Log
layout: default
---

# Api Query Log

Adds query log output to Api responses

# Setup

This feature requires the [Api listener]({{site.url}}/docs/listeners/api.html) to work

This listener will only append the `queryLog` key if `debug` is 2 or greater

## Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    $this->Crud->addListener('Api');
    $this->Crud->addListener('ApiQueryLog');
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
      'listeners' => ['Api', 'ApiQueryLog']
    ];

}
?>
{% endhighlight %}

# Configuration

The listener do not have any configuration options

This listener will only append the `queryLog` key if `debug` is 2 or greater

# Example output

{% highlight json %}
{
   "success": true,
   "data": [

   ],
   "queryLog": {
      "default": {
         "log": [
            {
               "query": "SELECT SOMETHING FROM SOMEWHERE",
               "params": [

               ],
               "affected": 25,
               "numRows": 25,
               "took": 0
            },
            {
               "query": "SELECT SOMETHING FROM SOMEWHERE'",
               "params": [

               ],
               "affected": 1,
               "numRows": 1,
               "took": 0
            }
         ],
         "count": 2,
         "time": 0
      }
   }
}
{% endhighlight %}
