**Table of Contents**

- [Introduction](#introduction)
- [Installation](#installation)
	- [Requirements](#requirements)
	- [Cloning and loading](#cloning-and-loading)
		- [With a simple git clone](#with-a-simple-git-clone)
		- [As a git submodule](#as-a-git-submodule)
- [Loading and installation](#loading-and-installation)
	- [Configuration](#configuration)
	- [Convention](#convention)
	- [Language](#language)
	- [Additional usage information](#additional-usage-information)
	- [Error and Success elements](#error-and-success-elements)
- [Event system](#event-system)
	- [Global accessible subject properties](#global-accessible-subject-properties)
	- [Crud actions and their events](#crud-actions-and-their-events)
		- [index()](#index)
		- [add()](#add)
		- [edit()](#edit)
		- [view()](#view)
		- [delete()](#delete)
	- [Subscribing to an event](#subscribing-to-an-event)
		- [Full event class](#full-event-class)
		- [A lamba / Closure](#a-lamba--closure)
		- [A method in your controller](#a-method-in-your-controller)
- [Filling Related Models select boxes](#filling-related-models-select-boxes)
	- [Related models' list events](#related-models-list-events)
	- [Example](#example)

# Introduction

The Crud plugin allow high re-usability of the default Create, Retrieve, Update and Delete (CRUD) actions in your controllers

Usually the code for CRUD is very simple, and always look the same - this plugin will add the actions to your controller so you don't have to re-implement them over and over

It only works with CakePHP > 2.1 - as it utilizes the new event system

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

# Loading and installation
Add the following to your __app/Config/bootstrap.php__

```php
<?php
CakePlugin::load('Crud', array('bootstrap' => true, 'routes' => true));
?>
```

In your (App)Controller load the Crud component and add required method

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
 * @var array
 */
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			'actions' => array('index', 'add', 'edit', 'view', 'delete')
		)
	);

/**
 * Dispatches the controller action.	 Checks that the action exists and isn't private.
 *
 * If Cake raises MissingActionException we attempt to execute Crud
 *
 * @param CakeRequest $request
 * @return mixed The resulting response.
 * @throws PrivateActionException When actions are not public or prefixed by _
 * @throws MissingActionException When actions are not defined and scaffolding and CRUD is not enabled.
 */
	public function invokeAction(CakeRequest $request) {
		try {
			return parent::invokeAction($request);
		} catch (MissingActionException $e) {
			// Check for any dispatch components
			if (!empty($this->dispatchComponents)) {
				// Iterate dispatchComponents
				foreach ($this->dispatchComponents as $component => $enabled) {
					// Skip them if they aren't enabled
					if (empty($enabled)) {
						continue;
					}

					// Skip if isActionMapped isn't defined in the Component
					if (!method_exists($this->{$component}, 'isActionMapped')) {
						continue;
					}

					// Skip if the action isn't mapped
					if (!$this->{$component}->isActionMapped($request->params['action'])) {
						continue;
					}

					// Skip if executeAction isn't defined in the Component
					if (!method_exists($this->{$component}, 'executeAction')) {
						continue;
					}

					// Execute the callback, should return CakeResponse object
					return $this->{$component}->executeAction();
				}
			}

			// No additional callbacks, re-throw the normal Cake exception
			throw $e;
		}
	}
}
?>
```

## Configuration

We only use routes without prefix in these examples, but the Crud component works with __any__ prefixes you may have. It just requires some additional configuration.

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

In the above example, if /delete is called on __any__ controller, cake will raise it's normal missing action error as if nothing has happened

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

The Crud component always operates on the __$modelClass__ of your controller, that's the first model in your $uses array

There is no view for delete action, it will always redirect

Default `$components` variable according to the documentation is like this:

```php
public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
        'actions' => array('index', 'add', 'edit', 'view', 'delete')
    )
);
```

In this case, it will assume that all the IDs of your models are made with integers.

If they are `UUID indexed`, please add the following setting:

```php
public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
        'actions' => array('index', 'add', 'edit', 'view', 'delete'),
        'validateId' => 'uuid'
    )
);
```

In the `Index` views:

The paginated array is in $items. So if you have Baked an Index view, set in the beginning something like this:

```php
$users = $items;
```

The rest of the view will work. Otherwise you will get an error message about undefined variable $users.

In the `View` views:

Add the following at the beginning of the page.

```php
$user = $item;
```

## Error and Success elements

This plugin uses `error.ctp` and `success.ctp` to display Flash messages.

So create the following:
* `Views/Elements/error.ctp`
* `Views/Elements/success.ctp`

In each, the message passed is in the variable `$message` as usual.

# Event system

The CRUD plugin uses the new event system introduced in Cake 2.1

## Global accessible subject properties

The subject object can be accessed through __$event->subject__ in all event callbacks

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

## Language

All of the messages used in the crud component can be overridden in one of two ways: by explicitly defining the messages
to use in the controller's components array, or by using the standard translations functions of CakePHP.

### Overriding individual messages

The below components array is populated with all of the messages used:

```php
<?php
class DemoController extends AppController {

/**
 * List of global controller components
 *
 * @cakephp
 * @var array
 */
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			'actions' => array('index', 'add', 'edit', 'view', 'delete'),
			'translations' => array(
				'domain' => 'crud',
				'name' => null,
				'create' => array(
					'success' => array(
						'message' => 'Successfully created {name}',
						'element' => 'success'
					),
					'error' => array(
						'message' => 'Could not create {name}',
						'element' => 'error'
					)
				),
				'update' => array(
					'success' => array(
						'message' => '{name} was successfully updated',
						'element' => 'success'
					),
					'error' => array(
						'message' => 'Could not update {name}',
						'element' => 'error'
					)
				),
				'delete' => array(
					'success' => array(
						'message' => 'Successfully deleted {name}',
						'element' => 'success'
					),
					'error' => array(
						'message' => 'Could not delete {name}',
						'element' => 'error'
					)
				),
				'find' => array(
					'error' => array(
						'message' => 'Could not find {name}',
						'element' => 'error'
					)
				),
				'invalid_http_request' => array(
					'error' => array(
						'message' => 'Invalid HTTP request',
						'element' => 'error'
					),
				),
				'invalid_id' => array(
					'error' => array(
						'message' => 'Invalid id',
						'element' => 'error'
					)
				)
			)
		)
	);
}
```

The `Crud.Crud.translations.name` key, if defined, overrides the model's name property, and is
used to replace the `{name}` placeholder in the messages for each CRUD action. If it is not set,
the model's name property is used.

### Using translations

The strings indicated in the above code block are converted to complete sententces and then passed
through Cake's translate functions](http://book.cakephp.org/2.0/en/core-libraries/internationalization-and-localization.html).
By default, the translation domain `crud` is used in translations, this can be overriden by setting the domain to a different
value in the components poperty.

For convenience, a shell is provided to generate full-sentence translation calls to permit [Cake's I18n
shell](http://book.cakephp.org/2.0/en/console-and-shells/i18n-shell.html)
to extract them

```sh
$ Console/cake Crud.translations generate
---------------------------------------------------------------
Generating translation strings for models: Post

Adding: Invalid HTTP request
Adding: Invalid id
Adding: Successfully created Post
Adding: Could not create Post
Adding: Post was successfully updated
Adding: Could not update Post
Adding: Successfully deleted Post
Adding: Could not delete Post
Adding: Could not find Post
app/Config/i18n_crud.php updated
---------------------------------------------------------------
$
```

The contents of the file `app/Config/i18n_crud.php` is only calls to the translate function:

```php
<?php

/**
 * Common CRUD Component translations
 */
__d('crud', 'Invalid HTTP request');
__d('crud', 'Invalid id');

/**
 * Post CRUD Component translations
 */
__d('crud', 'Successfully created Post');
__d('crud', 'Could not create Post');
__d('crud', 'Post was successfully updated');
__d('crud', 'Could not update Post');
__d('crud', 'Successfully deleted Post');
__d('crud', 'Could not delete Post');
__d('crud', 'Could not find Post');
```

This file provides static calls of all permutations of the messages that the component could use for the App's
models. To generate the calls for a plugin's models - pass the path to the plugin as an argument:

```sh
$ Console/cake Crud.translations generate Plugin/Foo
---------------------------------------------------------------
Generating translation strings for models: Foo

Adding: Invalid HTTP request
Adding: Invalid id
Adding: Successfully created Foo
Adding: Could not create Foo
Adding: Foo was successfully updated
Adding: Could not update Foo
Adding: Successfully deleted Foo
Adding: Could not delete Foo
Adding: Could not find Foo
app/Config/i18n_crud.php updated
---------------------------------------------------------------
$
```

In the same way you can define/add the translations for an individual model.

The config file generated by this shell is not loaded at run time, it's purpose is purely to provide fixed-string translations
for the extract task to be able to identify the sentences used in the crud plugin. It's recommended to add this file to your
application's code repository.

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

# Filling Related Models select boxes

If you are used to bake or CakePHP scaffolding you might want to have some control over the data it is sent to the view for
filling select boxes for associated models. Crud component can be configured to return the list of record for all related models
or just those you want to in a per-action basis

By default all related model lists for main Crud component model instance will be fetched, but only for `add`, `edit` and corresponding
admin actions. For instance if your `Post` model in associated to `Tag` and `Author`, then for the aforementioned actions you will have
in your view the `authors` and `tags` variable containing the result of calling find('list') on each model.

Should you need more fine grain control over the lists fetched, you can configure statically or use dynamic methods:

```php
<?php
class DemoController extends AppController {
   /**
	* List of global controller components
	*
	* @cakephp
	* @var array
	*/
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			'actions' => array('index', 'add', 'edit', 'view', 'delete'),
			'relatedList' => array(
				'add' => array('Author'), //Only set $authors variable in the view for action add and admin_add
				'edit' => array('Tag', 'Cms.Page'), //Set $tags and $pages variable. Page model from plugin Cms will be used
				// As admin_edit is not listed here it will use defaults from edit action
			)
		)
	);
}
?>
```

You can also configure default to not repeat yourself too much:

```php
<?php
class DemoController extends AppController {
   /**
	* List of global controller components
	*
	* @cakephp
	* @var array
	*/
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			'actions' => array('index', 'add', 'edit', 'view', 'delete'),
			'relatedList' => array(
				'default' => array('Author'),
				'add' => true, // add action is enabled and will fetch Author by default
				'admin_change' => true, // admin_change action is enabled and will fetch Author by default
				'edit' => array('Tag'), //edit action is enabled and will only fetch Tags
				'admin_edit' => false // admin_edit action is disabled, no related models will be fetched
			)
		)
	);
}
?>
```

If configuring statically is not your thing, or you want to dynamically fetch related models based on some conditions, then you can
call `mapRelatedList` and `enableRelatedList` function in CrudComponent:


```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Crud->enableRelatedList(array('index', 'delete'));
		$this->mapRelatedList(array('Author', 'Cms.Page'), 'default'); // By default all enabled actions should fetch Author and Page
	}


	public function delete() {
		$this->mapRelatedList(array('Author'), 'default'); // Only fetch authors list
		$this->Crud->executeAction('delete');
	}

}
?>
```

## Related models' list events

If for any reason you need to alter the query or final results generated by fetching related models lists, you can use `Crud.beforeListRelated` and
`Crud.afterListRelated` events to inject your own logic.

`Crud.beforeListRelated` wil receive the following parameters in the event subject, which can be altered on the fly before any result is fetched

	* query: An array with options for find('list')
	* model: Model instance, the model to be used for fiding the list or records


`Crud.afterListRelated` wil receive the following parameters in the event subject, which can be altered on the fly after results were fetched

	* items: result from calling find('list')
	* viewVar: Variable name to be set on the view with items as value
	* model: Model instance, the model to be used for fiding the list or records


## Example

```php
<?php
class DemoController extends AppController {
	//...

	public function beforeFilter() {
		parent::beforeFilter();

		//Authors list should only have the 3 most recen items
		$this->Crud->on('beforeListRelated', function($event) {
			if ($event->subject->model instanceof Author) {
				$event->subject->query['limit'] = 3;
				$event->subject->query['order'] = array('Author.created' => 'DESC');
			}
		});

		$this->Crud->on('afterListRelated', function($event) {
			if ($event->subject->model instanceof Tag) {
				$event->subject->items += array(0 => 'N/A');
				$event->subject->viewVar = 'labels';
			}
		});
	}

}
?>
```
