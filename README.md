# Introduction

The Crud plugin allow high reusability of the default Create, Retrieve, Update and Delete (CRUD) actions in your controllers

Usually the code for CRUD is very simple, and always look the same - this plugin will add the actions to your controller so you don't have to reimplement them over and over

It only works with CakePHP 2.1 - as it utilizes the new event system

The plugin requires a PSR-0 autoloader, if you don't have one, please install https://github.com/nodesagency/Platform-Common-Plugin

# Installation

## Requirements

* CakePHP 2.1
* PHP 5.3
* PSR-0 class loader

## Cloning and loading

### With a simple git clone

```
git clone git://github.com/nodesagency/Platform-Crud-Plugin.git app/Plugin/Crud
```

### As a git submodule

```
git submodule add git://github.com/nodesagency/Platform-Crud-Plugin.git app/Plugin/Crud
```

# Loading
Add the following to your __app/Config/bootstrap.php__ - make sure to include the __bootstrap__ key

```php
<?php
CakePlugin::load('Crud', array('bootstrap' => true));
```

In your (app) controller load the Crud component

```php
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
    * @cakephp
    * @var array
    */
    public $components = array(
        // Enable CRUD actions
        'Crud.Crud' => array(
            'actions' => array('index', 'add', 'edit', 'view', 'delete');
        )
    );
}
?>
```

## Configuration

We only use routes without prefix in these examples, but the Crud component works with any prefixes you may have. It just requires some additional configuration.

In the code example above, we pass in an actions array with all the controller actions we wish the Crud component to handle - you can easily omit some of the actions

```php
<?php
public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
         // All actions but delete() will be implemented
        'actions' => array('index', 'add', 'edit', 'view') 
    )
);
```

In the above example, if /delete is called on the controller, cake will raise it's normal missing action error as if nothing has happend

You can enable and disable Crud actions on the fly

```php
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
    * @cakephp
    * @var array
    */
    public $components = array(
        // Enable CRUD actions
        'Crud.Crud' => array(
            'actions' => array('index', 'add', 'edit', 'view', 'delete')
        )
    );
 
    public function beforeFilter() {
        // Will ignore delete action
        $this->Crud->disableAction('delete'); 
        
        // Will process delete action again
        $this->Crud->enableAction('delete');
        
        parent::beforeFilter();
    }
}
```

You can also change the default view used for a Crud action

```php
<?php
public function beforeFilter() {
    // Change the view for add crud action to be "public_add.ctp"
    $this->Crud->mapActionView('add',  'public_add');
 
    // Change the view for edit crud action to be "public_edit.ctp"
    $this->Crud->mapActionView('edit', 'public_edit');
 
    // Convenient shortcut to change both at once
    $this->Crud->mapActionView(array('add' => 'public_add', 'edit' => 'public_edit'));
    
    parent::beforeFilter();
}
```

## Convention

The Crud component always operates on the modelClass of your controller, that is the first model in your $uses array

By default Crud component asumes your add and edit views is identical, and will render them both with a "form.ctp" file.

There is no view for delete action, it will always redirect

### Event system

The CRUD plugin uses the new event system introduced in Cake 2.1

#### Global accessible subject properties

The subject object can be accessed through $event->subject in all event callbacks

<table>
<thead>
	<tr>
	 	<th>Name</th>
	 	<th>Value Type</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>self</td>
		<td>CrudComponent</td>
		<td>A reference to the CRUD component</td>
	</tr>
	<tr>
		<td>controller</td>
		<td>AppController</td>
		<td>A reference to the controller handling the current request</td>
	</tr>
	<tr>
		<td>model</td>
		<td>AppModel</td>
		<td>A reference to the model Crud is working on</td>
	</tr>
	<tr>
		<td>request</td>
		<td>CakeRequest</td>
		<td>A reference to the CakeRequest for the current request</td>
	</tr>
	<tr>	
		<td>action</td>
		<td>string</td>
		<td>The current controller action being requested</td>
	</tr>
</tbody>
</table>

### Crud actions and their events

All Crud events always return void, any modifications should be done to the Crud\Subject object

All Crud events take exactly one parameter, \CakeEvent $event

The CRUD component emit the following events

#### index()

<table>
<thead>
	<tr>
	 	<th>Event</th>
	 	<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforePaginate</td>
		<td>$subject->controller->paginate</td>
		<td>Modify any pagination settings</td>
	</tr>
	<tr>
		<td>Crud.afterPaginate</td>
		<td>$subject->items</td>
		<td>You can modify the pagination result if needed, passed as $items</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered</td>
	</tr>
</tbody>
</table>

#### add()

<table>
<thead>
	<tr>
	 	<th>Event</th>
	 	<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeSave</td>
		<td>N/A</td>
		<td>Access and modify the data from the $request object</td>
	</tr>
	<tr>
		<td>Crud.afterSave</td>
		<td>$subject->id</td>
		<td>$id is only available if the save was successful</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered</td>
	</tr>
</tbody>
</table>

#### edit()

<table>
<thead>
	<tr>
	 	<th>Event</th>
	 	<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeSave</td>
		<td>N/A</td>
		<td>Access and modify the data from the $request object</td>
	</tr>
	<tr>
		<td>Crud.afterSave</td>
		<td>$subject->id</td>
		<td>$id is only available if the save was successful</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as $queryParams in behavior beforeFind()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record</td>
	</tr>
	<tr>
		<td>Crud.afterFind</td>
		<td>N/A</td>
		<td>Modify the record found by find() and return it</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered</td>
	</tr>
</tbody>
</table>

#### view()

<table>
<thead>
	<tr>
	 	<th>Event</th>
	 	<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as $queryParams in behavior beforeFind()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record</td>
	</tr>
	<tr>
		<td>Crud.afterFind</td>
		<td>N/A</td>
		<td>Modify the record found by find() and return it</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered</td>
	</tr>
</tbody>
</table>

#### delete()

<table>
<thead>
	<tr>
	 	<th>Event</th>
	 	<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as $queryParams in behavior beforeFind()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record</td>
	</tr>
	<tr>
		<td>Crud.beforeDelete</td>
		<td>N/A</td>
		<td>Stop the delete by redirecting away from the action</td>
	</tr>
	<tr>
		<td>Crud.afterDelete</td>
		<td>N/A</td>
		<td>Modify the record found by find() and return it</td>
	</tr>
</tbody>
</table>

### Subscribing to an event

I would recommend using the Event class if you need to subscribe to more than one event

#### Full event class

The event class can be anywhere, but the default is inside app/Plugin/<plugin>/Lib/Crud/Event, if you use the PSR-0 autloader

Make sure you have configured the class loader inside app/Plugin/<plugin>/Config/bootstrap.php 

```php
<?php
// Can use any PSR-0 autoloader

// Replace Api with your plugin name
Nodes\Autoload::addPath(App::pluginPath('Api') . 'Lib' . DS);
```

Your Event class should look like this:

```php
<?php
namespace Crud\Event;

class Demo extends \Crud\BaseEvent {
	public function beforeRender(\CakeEvent $event) {
		// Check about this is admin, and about this function should be process for this action
		if ($event->subject->controller->isAdminRequest() && $event->subject->shouldProcess('only', array('admin_add'))) {
			// We only wanna do something, if this is admin request, and only for "admin_add"
		}
	}

	public function afterSave(\CakeEvent $event) {
		if ($event->subject->controller->isAdminRequest()) {
			// In this test, we want afterSave to do one thing, for admin_add and another for admin_edit
			// If admin_add redirect to index
			if ($event->subject->shouldProcess('only', array('admin_add'))) {
				if ($event->subject->success) {
					$event->subject->controller->redirect(array('action' => 'index'));
				}
			}
			// If admin_edit redirect to self
			elseif ($event->subject->shouldProcess('only', array('admin_edit'))) {
				if ($event->subject->success) {
					$event->subject->controller->redirect(array('action' => 'edit', $id));
				}
			}
		}
	}
}
``` 

and the controller

```php
<?php
public function beforeFilter() {
    parent::beforeFilter();
    $this->getEventManager()->attach(new Crud\Event\Demo());
}
```

#### A lamba / Closure

```php
<?php
public function beforeFilter() {
	parent::beforeFilter();
	$this->getEventManager()->attach(function(\CakeEvent $event) { debug($event->subject->query); }, 'Crud.beforePaginate');
}
```

#### A method in your controller

```php
<?php
public function beforeFilter() {
	parent::beforeFilter();
	$this->getEventManager()->attach(array($this, 'demoEvent'), 'Crud.beforePaginate');
}

public function demoEvent(\CakeEvent $event) {
	debug($event->subject->query);
}
```