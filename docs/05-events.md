# Crud actions and their events

All Crud events always return `NULL`, any modifications should be done to the CrudEventSubject object (`$event->subject`)

All Crud events take exactly one parameter, `CakeEvent $event`

For a list of emitted events, please see the `configuration` documentation

## Global accessible subject properties

The `$subject` object can be accessed through `$event->subject` in all event callbacks

<table>
<thead>
	<tr>
		<th>Property name</th>
		<th>Type</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>crud</td>
		<td>CrudComponent</td>
		<td>A reference to the CRUD component</td>
	</tr>
	<tr>
		<td>controller</td>
		<td>AppController</td>
		<td>A reference to the controller handling the current request</td>
	</tr>
	<tr>
		<td>collection</td>
		<td>ComponentCollection</td>
		<td>The original ComponentCollection that was injected into CrudComponent</td>
	</tr>
	<tr>
		<td>model</td>
		<td>AppModel</td>
		<td>A reference to the model Crud is working on</td>
	</tr>
	<tr>
		<td>modelClass</td>
		<td>string</td>
		<td>The modelClass property from the controller - usually the same as the model alias</td>
	</tr>
	<tr>
		<td>action</td>
		<td>string</td>
		<td>The request action name. This doesn't always match the request objects action property</td>
	</tr>
	<tr>
		<td>request</td>
		<td>CakeRequest</td>
		<td>A reference to the CakeRequest for the current request</td>
	</tr>
	<tr>
		<td>response</td>
		<td>CakeResponse</td>
		<td>The current CakeResponse object</td>
	</tr>
</tbody>
</table>

# Callbacks in the controller

In all these examples there is no call to the `parent::` in `beforeFilter` - this is highly recommended to remember to include.

The `Crud->on()` method accepts anything that `is_callable` evaluate to true.

## Lambda / Closure in beforeFilter()

This is convenient for for very simple callbacks, or for callbacks shared between multiple actions

It's recommended to add your callbacks to controller action they need to be used in, as it makes the controller code much more coherent and easier to debug

Don't put too many lines of logic in a closure callback as it quickly gets messy, and it's very hard to unit test the isolated behavior of the callback.

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		$this->Crud->on('beforePaginate', function(CakeEvent $event) {
			$event->subject->query['conditions'] = array('is_active' => true);
			debug($event->subject->query);
		});
	}
}
?>
```

## Method inside the controller

Very much like the `Closure` example above, except the callback code is in a method of its own, that can be unit tested easier.

The method __must be public__, since it's called from outside the scope of the controller.

__Pro tip__: Prefix your callbacks with `_` and CakePHP will prevent the method to be called through the web.

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		$this->Crud->on('beforePaginate', array($this, '_demoCallback'));
	}

	public function _demoCallback(CakeEvent $event) {
		$event->subject->query['conditions']['is_active'] = true;
		debug($event->subject->query);
	}
}
?>
```

## Overriding implementedEvents() in the controller

You can override the `implementedEvents` method inside the controller and provide a list of `event => callback` for Crud.

The key is the Crud event name (Remember all events need the `Crud.` prefix) and the value is the name of the method in your controller that should be executed.

The method __must be public__, since it's called from outside the scope of the controller.

__Pro tip__: Prefix your callbacks with `_` and CakePHP will prevent the method to be called through the web.

```php
public function implementedEvents() {
	return parent::implementedEvents() + array(
		'Crud.beforeFind' => '_beforeFind',
		'Crud.beforeSave' => '_beforeSave',
	);
}

public function _beforeFind(CakeEvent $event) {

}

public function _beforeSave(CakeEvent $event) {

}
```

## Lambda / Closure inside a specific controller action

Very much like the other `Closure` examples above.

When implementing callbacks inside the controller action, it's very important to call the `executeAction` in `Crud`.

This will allow Crud to continue to do it's magic just as if the method didn't exist at all in the controller in the first place.

```php
<?php
class DemoController extends AppController {
	public function view($id = null) {
		$this->Crud->on('beforeFind', function(CakeEvent $event) use ($id) {
			$event->subject->query['conditions']['is_active'] = true;
		}

		// Important for the ViewCrudAction to be executed
		return $this->Crud->executeAction();
	}
}
?>
```

# Listener classes

Crud listeners must be inside app/Controller/Crud/Listener ( app/Plugin/$plugin/Controller/Crud/Listener for plugins)

The `CrudListener` class provides an implementation of all the available callbacks you can listen for in Crud.

You can override the methods as needed inside your own Listener class.

Below is an example of a `CrudListener`

```php
<?php
App::uses('CrudListener', 'Crud.Controller/Crud');

class DemoListener extends CrudListener {

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

Attaching the above Listener to your Crud component is done as below.

You simply attach it to the normal `CakeEventManager` inside your controller.

`Crud` share the same event manager as the controller for maximum flexibility.

```php
<?php
App::uses('DemoListener', 'Controller/Crud/Listener');

class DemoController extends AppController {
	public function beforeFilter() {
		$this->getEventManager()->attach(new DemoListener());
	}
}
?>
```
