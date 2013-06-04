# Crud actions and their events

All Crud events always return `NULL`, any modifications should be done to the CrudEventSubject object (`$event->subject`)

All Crud events take exactly one parameter, `CakeEvent $event`

For a list of emitted events, please see the `configuration` documentation

# Callbacks in the controller

## Lambda in beforeFilter

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

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		$this->Crud->on('beforePaginate', array($this, 'demoEvent'));
	}

	public function demoEvent(CakeEvent $event) {
		$event->subject->query['conditions']['is_active'] = true;
		debug($event->subject->query);
	}
}
?>
```

## Lambda inside a specific controller action

```php
<?php
class DemoController extends AppController {
	public function view($id = null) {
		$this->Crud->on('beforeFind', function(CakeEvent $event) {
			$event->subject->query['conditions']['is_active'] = true;
		}

		// Important for the ViewCrudAction to be executed
		return $this->Crud->executeAction();
	}
}
?>
```

# Listener classes

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
