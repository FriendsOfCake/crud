# Introduction

The Crud plugin allow high reusability of the default Create, Retrieve, Update and Delete (CRUD) actions in your controllers

Usually the code for CRUD is very simple, and always look the same - this plugin will add the actions to your controller so you don't have to reimplement them over and over

It only works with CakePHP 2.1 - as it utilizes the new event system

The plugin requires a PSR-0 autoloader, if you don't have one, please install https://github.com/nodesagency/Platform-Common-Plugin

# Installation

## Requirements

* CakePHP 2.1
* PHP 5.3

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
Add the following to your __app/Config/bootstrap.php__

```php
<?php
CakePlugin::load('Crud');
?>
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
			'actions' => array('index', 'add', 'edit', 'view', 'delete')
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
class AppController extends Controller {
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			 // All actions but delete() will be implemented
			'actions' => array('index', 'add', 'edit', 'view')
		)
	);
}
?>
```

In the above example, if /delete is called on the controller, cake will raise it's normal missing action error as if nothing has happened

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
?>
```

You can also change the default view used for a Crud action

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		// Change the view for add crud action to be "public_add.ctp"
		$this->Crud->mapActionView('add',  'public_add');

		// Change the view for edit crud action to be "public_edit.ctp"
		$this->Crud->mapActionView('edit', 'public_edit');

		// Convenient shortcut to change both at once
		$this->Crud->mapActionView(array('add' => 'public_add', 'edit' => 'public_edit'));

		parent::beforeFilter();
	}
}
?>
```

## Convention

The Crud component always operates on the $modelClass of your controller, that is the first model in your $uses array

By default Crud component assumes your add and edit views is identical, and will render them both with a "form.ctp" file.

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

All Crud events always return void, any modifications should be done to the CrudEventSubject object

All Crud events take exactly one parameter, CakeEvent $event

The CRUD component emits the following events

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

Crud events must be inside app/Controller/Event ( app/Plugin/<plugin>/Controller/Event for plugins)

Your Event class should look like this:

```php
<?php
App::uses('CrudBaseEvent', 'Crud.Controller/Event');

class DemoEvent extends CrudBaseEvent {
	public function beforeRender(CakeEvent $event) {
		// Check about this is admin, and about this function should be process for this action
		if ($event->subject->shouldProcess('only', array('admin_add'))) {
			// We only wanna do something, if this is admin request, and only for "admin_add"
		}
	}

	public function afterSave(CakeEvent $event) {
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
?>
```

and the controller

```php
<?php
App::uses('DemoEvent', 'Controller/Event');

class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(new DemoEvent());
	}
}
?>
```

#### A lamba / Closure

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(function(CakeEvent $event) { debug($event->subject->query); }, 'Crud.beforePaginate');
	}
}
?>
```

#### A method in your controller

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(array($this, 'demoEvent'), 'Crud.beforePaginate');
	}

	public function demoEvent(CakeEvent $event) {
		$event->subject->query['conditions']['is_active'] = true;
	}
}
?>
```

# Migration from legacy PSR-0 autoloader to more Cake feel

## Changes

* Crud used to have a Config/bootstrap.php file, its no longer have, please make sure to remove the bootstrap => true from CakePlugin::load('Crud')
* All event classes used to be in Lib/Crud/Event - they are now located in Controller/Event
* The files used to have a namespace \Crud\Event - thats no longer the case
* The classes used to extend from "Base" - they should now extend from CrudBaseEvent

## New

You must now load the classes on your own.

* In all your Event class files that extends "CrudBaseEvent" must have "App::uses('CrudBaseEvent', 'Crud.Controller/Event');" before the class declaration
* In all controllers where you attach the Crud Event to the event manager, you must load the Event class with "App::uses('DemoEvent', 'Controller/Event');" or "App::uses('DemoEvent', 'Plugin.Controller/Event');"

## Step by step

* Make sure that app/Config/bootstrap.php that loads Crud plugin doesn't load the bootstrap file
* Move all Event classes from Lib/Crud/Event to Controller/Event (both for App and Plugin folders)
* Remove all "namespace Crud\Event" from the classes
* Load CrudBaseEvent in each Event class ( App::uses('CrudBaseEvent', 'Crud.Controller/Event'); )
* Make sure all Event classes no longer extends from Base but from CrudBaseEvent
* Find all places where you attach Crud Events to the your EventManger ($this->getEventManager()->attach(..))
 * Make sure you load your Event class before your Controller Class declaration ( App::uses('DemoEvent', 'Plugin.Controller/Event'); )
 * Make sure you don't use "new \Crud\Event\$className" but the normal Event class name now (new DemoEvent();)

## Examples

### Before

```php
<?php
// app/Plugin/Demo/Lib/Crud/Event/Demo.php
namespace Crud\Event;

class Demo extends Base {

}

// app/Plugin/Demo/Controller/DemosController.php
class DemosController extends DemoAppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(new Crud\Event\Demo());
	}
}
?>
```

### After

```php
<?php
// app/Plugin/Demo/Controller/Event/DemoEvent.php
App::uses('CrudBaseEvent', 'Crud.Controller/Event');

class DemoEvent extends CrudBaseEvent {

}

// app/Plugin/Demo/Controller/DemoAppController.php
App::uses('DemoEvent', 'Demo.Controller/Event');

class DemosController extends DemoAppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(new DemoEvent());
	}
}
?>
```