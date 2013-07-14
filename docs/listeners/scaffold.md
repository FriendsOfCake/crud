---
title: Scaffold
layout: default
---

# Scaffold

Forces all Crud controller actions to use the build-in Scaffolding views

This is the exact same views as the `$scaffold` property in the controller would get you

Only with the added benefit of actually being able to modify the controller logic like normal using
Crud

# Setup

There is no requirements other than Crud to be enabled for `Scaffold` to work

## Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    $this->Crud->addListener('Scaffold');
    // Same as:
    $this->Crud->addListener('Crud.Scaffold');
    // Same as:
    $this->Crud->addListener('Scaffold', 'Crud.Scaffold');
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
    'Crud.Crud' => [
      'actions' => ['index', 'view' ],
      'listeners' => [
        'Scaffold'
        // Same as
        'Crud.Scaffold',
        // Same as
        'Scaffold' => 'Crud.Scaffold'
      ]
    ];
}
?>
{% endhighlight %}

# Configuration

The listener do not have any configuration options
