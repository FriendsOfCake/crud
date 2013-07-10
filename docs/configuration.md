---
title: Configuration
layout: default
---

# Configuration

We only use routes without prefix in these examples, but the Crud component works with __any__ prefixes you may have. It just requires some additional configuration.

In the code example above, we pass in an actions array with all the controller actions we wish the Crud component to handle - you can omit some of the actions if you wish.

{% highlight php %}
<?php
class AppController extends Controller {

  public $components = [
    // Enable CRUD actions
    'Crud.Crud' => [
       // All actions but delete() will be implemented
      'actions' => [
        // The controller action 'index' will map to the IndexCrudAction
        'index' => 'Crud.Index',
        // The controller action 'add' will map to the AddCrudAction
        'add'   => 'Crud.Add',
        // The controller action 'edit' will map to the EditCrudAction
        'edit'  => 'Crud.edit',
        // The controller action 'view' will map to the ViewCrudAction
        'view'  => 'Crud.View'
      ]
    ]
  ];

}
?>
{% endhighlight %}

If you wish to modify the default settings, simply pass in an array as `value` for each action array key

{% highlight php %}
<?php
class AppController extends Controller {

  public $components = [
    // Enable CRUD actions
    'Crud.Crud' => [
       // All actions but delete() will be implemented
      'actions' => [
        // The controller action 'index' will still map to the IndexCrudAction
        'index' => ['viewVar' => 'items']
        // The controller action 'add' will map to the MyPlugin.Controller/Crud/Action/MyIndexCrudAction
        'add'   => ['className' => 'MyPlugin.MyIndex']
        // The controller action 'edit' will map to the EditCrudAction
        'edit'  => 'Crud.edit',
        // The controller action 'view' will map to the ViewCrudAction
        'view'  => 'Crud.View'
      ]
    ]
  ];

}
?>
{% endhighlight %}

In the above example, if the `delete` action is called on __any__ controller (that doesn't implement it on their own), cake will raise it's normal `MissingActionException` error

You can enable and disable Crud actions on the fly

{% highlight php %}
<?php
/**
 * Application wide controller
 *
 * @abstract
 * @package App.Controller
 */
abstract class AppController extends Controller {
/**
 * List of global controller components
 *
 * @var array
 */
  public $components = [
    // Enable CRUD actions
    'Crud.Crud' => [
      'actions' => [
        // Same as array('index' => 'Crud.Index')
        'index',
        // Same as array('add' => 'Crud.Add')
        'add',
        // Same as array('edit' => 'Crud.Edit')
        'edit',
        // Same as array('view' => 'Crud.View')
        'view',
        // Same as array('delete' => 'Crud.Delete')
        'delete'
      ]
    ]
  ];

  public function beforeFilter() {
    // Will ignore delete action
    $this->Crud->disable('delete');

    // Will process delete action again
    $this->Crud->enable('delete');

    parent::beforeFilter();
  }
}
?>
{% endhighlight %}

You can also change the default view used for a Crud action

{% highlight php %}
<?php
class DemoController extends AppController {
  public function beforeFilter() {
    // Change the view for add crud action to be "public_add.ctp"
    $this->Crud->view('add',  'public_add');
    // Same as
    $this->Crud->action('add')->view('public_add');

    // Change the view for edit crud action to be "public_edit.ctp"
    $this->Crud->view('edit', 'public_edit');
    // Same as
    $this->Crud->action('edit')->view('public_edit');

    // Convenient shortcut to change both at once
    $this->Crud->view(array('add' => 'public_add', 'edit' => 'public_edit'));
    // Same as
    $this->Crud->action('add')->view('public_add');
    $this->Crud->action('edit')->view('public_edit');

    parent::beforeFilter();
  }
}
?>
{% endhighlight %}

# Change the find() method

Changing the `find type` allows you to use [custom find methods](http://book.cakephp.org/2.0/en/models/retrieving-your-data.html#creating-custom-find-types) inside a `CrudAction`

By providing your own custom find method, you can easily inject more complex `find` logic and at the same time follow the [skinny controllers, fat models](http://www.mikebernat.com/images/cake/layercake.png) code guideline.

Adding custom data to your custom find is also possible, and very simple, have a look at the `events` documentation.

By default `index` uses `find('all')`

By default `view` uses `find('first')`

By default `add` uses `find('first')`

By default `edit` uses `find('first')`

By default `delete` uses `find('count')`

## Using beforeFilter

{% highlight php %}
<?php
public function beforeFilter() {
  // The index() function will call find('published') on your model
  $this->Crud->action('index')->findMethod('published');

  // Get the current configuration
  $config = $this->Crud->action('index')->findMethod();

  // The admin_index() function will call find('unpublished') on your model
  $this->Crud->action('admin_unpublished')->findMethod('unpublished');

  // Get the current configuration
  $config = $this->Crud->action('admin_index')->findMethod();
}
?>
{% endhighlight %}

## In the controller action

{% highlight php %}
<?php
// You don't have to provide the action name in 'action'
// since the default is the current action
public function index() {
  $this->Crud->action()->findMethod('published');

  // Get the current configuration
  $config = $this->Crud->action()->findMethod();

  return $this->Crud->executeAction();
}

public function admin_index() {
  $this->Crud->action()->findMethod('unpublished');

  // Get the current configuration
  $config = $this->Crud->action()->findMethod();

  return $this->Crud->executeAction();
}
?>
{% endhighlight %}

# Change the view to be rendered

By default Crud renders a view with the same name as the controller action, but there are cases where you want to change this.

If you action is `admin_index` the `admin_index.ctp` view will be rendered by default.

## Using beforeFilter

{% highlight php %}
<?php
public function beforeFilter() {
  // Change the 'index' view  to 'my_index'
  $this->Crud->action('index')->view('my_index');

  // Get the current configuration
  $config = $this->Crud->action('index')->view();

  $this->Crud->action('admin_index')->view('my_admin_index');

  // Get the current configuration
  $config = $this->Crud->action('admin_index')->view();
}
?>
{% endhighlight %}

## In the controller action

{% highlight php %}
<?php
// You don't have to provide the action name in 'action'
// since the default is the current action

public function index() {
  $this->Crud->action()->view('my_index');

  // Get the current configuration
  $config = $this->Crud->action()->view();

  return $this->Crud->executeAction();
}

public function admin_index() {
  $this->Crud->action()->view('my_admin_index');

  // Get the current configuration
  $config = $this->Crud->action()->view();

  return $this->Crud->executeAction();
}
?>
{% endhighlight %}

# Change the viewVar (variable name in the view)

By default Crud follows the convention of `$items` in `index()` actions and `$item` in `view()` actions

If you want to change these settings, simply call the `viewVar()` method on the action

## Using beforeFilter

{% highlight php %}
<?php
public function beforeFilter() {
  $this->Crud->action('index')->viewVar('data');

  // Get the current configuration
  $config = $this->Crud->action('index')->viewVar();

  $this->Crud->action('admin_index')->viewVar('data');

  // Get the current configuration
  $config = $this->Crud->action('admin_index')->viewVar();
}
?>
{% endhighlight %}

## In the controller action

{% highlight php %}
<?php
// You don't have to provide the action name in 'action'
// since the default is the current action

public function index() {
  $this->Crud->action()->viewVar('data');

  // Get the current configuration
  $config = $this->Crud->action()->viewVar();

  return $this->Crud->executeAction();
}

public function admin_index() {
  $this->Crud->action()->viewVar('data');

  // Get the current configuration
  $config = $this->Crud->action()->viewVar();

  return $this->Crud->executeAction();
}
?>
{% endhighlight %}

# Enable a Crud action on the fly

This can only be done in `beforeFilter` (or earlier in the request) since it's the last method called
before the actual controller action is executed.

Enabling a `CrudAction` automatically injects it into the Controller as if it was defined in the `actions` array in the crud component configuration.

{% highlight php %}
<?php
// This can only be done in the beforeFilter

public function beforeFilter() {
  $this->Crud->action('delete')->enable();
  $this->Crud->action('admin_delete')->enable();
}
?>
{% endhighlight %}

# Disable a Crud action on the fly

This can only be done in `beforeFilter` (or earlier in the request) since it's the last method called
before the actual controller action is executed.

Disabling a `CrudAction` automatically removes it from the Controller as if it was never defined in the `actions` array in the crud component configuration.

{% highlight php %}
<?php
// This can only be done in the beforeFilter

public function beforeFilter() {
  $this->Crud->action('delete')->disable();
  $this->Crud->action('admin_delete')->disable();
}
?>
{% endhighlight %}

# Change CrudAction configuration settings on the fly

{% highlight php %}
<?php
// This can be done both in beforeFilter and the controller action
// All possible config keys can be found in the CrudAction classes (app/Plugin/Crud/Controller/Crud/Action)
public function beforeFilter() {
  $this->Crud->action('view')->config('validateId', 'uuid');

  // Get the current configuration
  $config = $this->Crud->action('view')->config('validateId');
}
?>
{% endhighlight %}

# Disable secureDelete for delete() actions

`secureDelete` enforces that a `HTTP DELETE` request must be used for any `delete()` actions.

If set to `true` only `HTTP DELETE` is considered valid for `delete()` actions.

If set to `false` both `HTTP DELETE` and `HTTP POST` is considered valid for `delete()` actions.

The default setting is `true`

{% highlight php %}
<?php
// Disabling this feature allow HTTP POST to execute delete() actions
// This can be changed in both beforeFilter and the controller action
public function beforeFilter() {
  $this->Crud->action('delete')->config('secureDelete', false);

  // Get the current configuration
  $config = $this->Crud->action('delete')->config('secureDelete');
}
?>
{% endhighlight %}

# Configure related model data for add / edit views

Please see the [related data documentation]({{site.url}}/docs/listeners/related-data.html)

# Change save options

All options as described in [CakePHP documentation on saveAll()](http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-saveall-array-data-null-array-options-array) is valid here

This configuration maps directly to the 2nd parameter of `saveAll()` called `$options`

The default for `add` and `edit` is `array('validate' => 'first', 'atomic' => true)`

{% highlight php %}
<?php
// This can be changed in beforeFilter and in a controller action
public function beforeFilter() {
  // saveOptions is the 2nd argument to saveAll()
  $this->Crud->action('add')->saveOptions(array('atomic' => false));
}
?>
{% endhighlight %}

# Change the crud action class

Please remember that the CrudAction class names `index`, `view`, `add`, `edit` and `delete` is reserved, and can only be used inside the Crud plugin.

You own CrudActions can be called anything but these class names

Please see the [custom crud action documentationn]({{site.url}}/docs/actions/custom.html) for more information

## Through component configuration

{% highlight php %}
<?php
class AppController extends Controller {

  public $components = [
    // Enable CRUD actions
    'Crud.Crud' => [
       // All actions but delete() will be implemented
      'actions' => [
        // The controller action 'add' will map to the MyPlugin.Controller/Crud/Action/MyIndexCrudAction
        'index' => ['className' => 'MyPlugin.MyIndex']
        // The controller action 'add' will map to the MyPlugin.Controller/Crud/Action/MyAddCrudAction
        'add'   => ['className' => 'MyPlugin.MyAdd']
        // The controller action 'add' will map to the MyPlugin.Controller/Crud/Action/MyEditCrudAction
        'edit'  => ['className' => 'MyPlugin.MyEdit']
        // The controller action 'add' will map to the MyPlugin.Controller/Crud/Action/MyViewCrudAction
        'view'  => ['className' => 'MyPlugin.MyView']
      ]
    ]
  ];

}
?>
{% endhighlight %}

## On The fly

{% highlight php %}
<?php
// This can be changed in beforeFilter and in a controller action
public function beforeFilter() {
  // saveOptions is the 2nd argument to saveAll()
  $this->Crud->defaults('action', 'index', ['className' => 'MyPlugin.MyAction']);
}
?>
{% endhighlight %}
