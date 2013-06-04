## Crud actions and their events

All Crud events always return void, any modifications should be done to the CrudEventSubject object ($event->subject)

All Crud events take exactly one parameter, CakeEvent $event

The CRUD component emits the following events

### index()

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

### add()

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

### edit()

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

### view()

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

### delete()

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

## Subscribing to an event

I would recommend using the Event class if you need to subscribe to more than one event

### Full event class

Crud events must be inside app/Controller/Event ( app/Plugin/$plugin/Controller/Event for plugins)

Your Event class should look like this:

```php
<?php
App::uses('CrudListener', 'Crud.Controller/Event');

class DemoEvent extends CrudListener {
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

### A lamba / Closure

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Crud->on('Crud.beforePaginate', function(CakeEvent $event) { debug($event->subject->query); });
	}
}
?>
```

### A method in your controller

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Crud->on('Crud.beforePaginate', array($this, 'demoEvent'));
	}

	public function demoEvent(CakeEvent $event) {
		$event->subject->query['conditions']['is_active'] = true;
	}
}
?>
```
