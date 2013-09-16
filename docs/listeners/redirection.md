---
title: Redirection Listener
layout: default
---

# Redirection listener

Enable more complex redirection rules

# Setup

Simply load the listener, all Crud actions already provide some sane defaults

## Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    $this->Crud->addListener('Redirection');

    parent::beforeFilter();
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
      'actions' => ['index', 'view'],
      'listeners' => ['Redirection']
    ];

}
?>
{% endhighlight %}

# Configuration

## Readers

A `reader` is a [closure](http://php.net/closure) that can access a field in an object through different means

Below is a list of the build-in readers you can use

<table class="table">
<thead>
  <tr>
    <th>Name</th>
    <th>Pseudo code</th>
    <th>Description</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>request.key</td>
    <td><code>$CakeRequest->{$field}</code></td>
    <td>Access a property directly on the CakeRequest object</td>
  </tr>
  <tr>
    <td>request.data</td>
    <td><code>$CakeRequest->data($field)</code></td>
    <td>Access a HTTP POST data field using Hash::get() compatible format</td>
  </tr>
  <tr>
    <td>request.query</td>
    <td><code>$CakeRequest->query($field)</code></td>
    <td>Access a HTTP query argument using Hash::get() compatible format</td>
  </tr>
  <tr>
    <td>model.key</td>
    <td><code>$Model->{$field}</code></td>
    <td>Access a property directly on the Model instance</td>
  </tr>
  <tr>
    <td>model.data</td>
    <td><code>$Model->data[$field]</code></td>
    <td>Access a model data key using Hash::get() compatible format</td>
  </tr>
  <tr>
    <td>model.field</td>
    <td><code>$Model->field($field)</code></td>
    <td>Access a model key by going to the database and read the value</td>
  </tr>
  <tr>
    <td>subject.key</td>
    <td><code>$CrudSubject->{$key}</code></td>
    <td>Access a property directly on the event subject</td>
  </tr>
</tbody>
</table>

## Adding your own reader

Adding or overriding a reader is very simple

The closure takes two arguments:

1) <code>CrudSubject $subject</code>

2) <code>$key = null</code>

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    $listener = $this->Crud->listener('Redirection');
    $listener->reader($name, Closure $closure);

    // Example on a reader using Configure
    $listener->reader('configure.key', function(CrudSubject $subject, $key)) {
      return Configure::read($key);
    });

    parent::beforeFilter();
  }
}
?>
{% endhighlight %}

# Action defaults

Below is the defaults provided by build-in Crud actions

## Add action

By default Add Crud Action always redirect to `array('action' => 'index')` on `afterSave`

<table class="table">
<thead>
  <tr>
    <th>Reader</th>
    <th>Key</th>
    <th>Result</th>
    <th>Description</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><code>request.data</code></td>
    <td><code>_add</code></td>
    <td><code>array('action' => 'add')</code></td>
    <td>By providing <code>_add</code> as a post key, the user will be redirected back to the add action</td>
  </tr>
  <tr>
    <td><code>request.data</code></td>
    <td><code>_edit</code></td>
    <td><code>array('action' => 'edit', $id)</code></td>
    <td>By providing <code>_edit</code> as a post key, the user will be redirected to the edit action with the newly created ID as parameter</td>
  </tr>
</tbody>
</table>

## Edit action

By default Edit Crud Action always redirect to `array('action' => 'index')` on `afterSave`

<table class="table">
<thead>
  <tr>
    <th>Reader</th>
    <th>Key</th>
    <th>Result</th>
    <th>Description</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><code>request.data</code></td>
    <td><code>_add</code></td>
    <td><code>array('action' => 'add')</code></td>
    <td>By providing <code>_add</code> as a post key, the user will be redirected to the <code>add</code> action</td>
  </tr>
  <tr>
    <td><code>request.data</code></td>
    <td><code>_edit</code></td>
    <td><code>array('action' => 'edit', $id)</code></td>
    <td>By providing <code>_edit</code> as a post key, the user will be redirected back to edit action with the same ID as parameter as the current URL</td>
  </tr>
</tbody>
</table>

# Configuring your own redirection rules

It's very simple to modify existing or add your own redirection rules

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    // Get all the redirection rules
    $rules = $this->Crud->action()->redirection();

    // Get one named rule only
    $rule = $this->Crud->action()->redirection('add');

    // Configure a redirection rule:
    //
    // if $_POST['_view'] is set then redirect to
    // 'view' action with the value of '$subject->id'
    $this->Crud->action()->redirection('view',
      [
        'reader' => 'request.data',  // Any reader from the list above
        'key'    => '_view',         // The key to check for, passed to the reader
        'url'    => [                // The url to redirect to
          'action' => 'view',        // The final url will be '/view/$id'
          ['subject.key', 'id']      // If an array is encountered, it will be expanded the same was as 'reader'+'key'
        ]
      ]
    );

    parent::beforeFilter();
  }
}
?>
{% endhighlight %}
