---
title: Custom Listeners
layout: default
---

# Custom Crud Listeners

Crud listeners must be inside `app/Controller/Crud/Listener` ( `app/Plugin/$plugin/Controller/Crud/Listener` for plugins)

The `CrudListener` class provides an implementation of all the available callbacks you can listen for in Crud.

You can override the methods as needed inside your own Listener class.

Below is an example of a `CrudListener`

{% highlight php %}
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
{% endhighlight %}

Attaching the above Listener to your Crud component is done as below.

You simply attach it to the normal `CakeEventManager` inside your controller.

`Crud` share the same event manager as the controller for maximum flexibility.

{% highlight php %}
<?php
App::uses('DemoListener', 'Controller/Crud/Listener');

class DemoController extends AppController {

	public function beforeFilter() {
		$this->getEventManager()->attach(new DemoListener());
	}

}
?>
{% endhighlight %}
