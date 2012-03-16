# Introduction

The Crud plugin allow high reusability of the default Create, Retrieve, Update and Delete (CRUD) actions in our controllers

Usually the forms is very simple, and always look the same - this plugin will add the actions to your controller so you don't have to reimplement them over and over

It only works with CakePHP 2.1 - as it utilizes the new event system

The plugin requires a PSR-0 autoloader, if you don't have one, please install https://github.com/nodesagency/Platform-Common-Plugin


# Installation
## Requirements
CakePHP 2.1
PHP 5.3
PSR-0 class loader

## Loading the plugin
Make sure Cake loads the plugin by adding the following to your  app/Config/bootstrap.php - make sure to include bootstrap inclucion

CakePlugin::load('Crud', array('bootstrap' => true));

In your app controller make sure to load the Crud component
<code><pre><?php
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
</pre></code>

## Configuration
We only use routes without prefix, "admin_" is also supported by default 
In the code example above, we pass in an actions array with all the controller actions we wish the Crud component to handle - you can easily omit some of the actions
<code><pre>public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
        'actions' => array('index', 'add', 'edit', 'view') // All actions but delete() will be implemented
    )
);
</pre></code>
In the above example, if /delete is called on the controller, cake will raise it's normal error as if nothing has happend

You can enable and disable Crud actions on the fly
<code><pre><?php
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
</pre></code>
You can also change the default view used for a Crud action

<code><pre>
public function beforeFilter() {
    // Change the view for add crud action to be "public_add.ctp"
    $this->Crud->mapActionView('add',  'public_add');
 
    // Change the view for edit crud action to be "public_edit.ctp"
    $this->Crud->mapActionView('edit', 'public_edit');
 
    // Convenient shortcut to change both at once
    $this->Crud->mapActionView(array('add' => 'public_add', 'edit' => 'public_edit'));
    
    parent::beforeFilter();
}
</pre></code>
Convention
The Crud component always operates on the modelClass of your controller, that is the first model in your $uses array

By default Crud component asumes your add and edit actions is identical, and will attempt to render the "form.ctp" file 

There is no view for delete action, it will always just redirect

Event system

The CRUD plugin uses the new event system introduced in Cake 2.1

Global accessible subject properties
The subject object can be accessed through $event->subject in all event callbacks


Name  Value Type	Description
self	CrudComponent	A reference to the CRUD component
controller	AppController	A reference to the controller handling the current request
model	AppModel	A reference to the model Crud is working on
request	CakeRequest	A reference to the CakeRequest for the current request
action	string	The current controller action being requested
Crud actions and their events
All Crud events always return void, any modifications should be done to the Crud\Subject object


All Crud events take exactly one parameter, \CakeEvent $event
The CRUD component emit the following events

index()
Event	Subject modifiers	Description
Crud.init	
Initialize method
Crud.beforePaginate	$subject->controller->paginate	Modify any pagination settings
Crud.afterPaginate	$subject->items	You can modify the pagination result if needed, passed as $items
Crud.beforeRender	
Invoked right before the view will be rendered
add()
Event	Subject modifiers	Description
Crud.init	
Initialize method
Crud.beforeSave	
Access and modify the data from the $request object
Crud.afterSave	
$id is only available if the save was successful
Crud.beforeRender	
Invoked right before the view will be rendered
edit()
Event	Subject modifiers	Description
Crud.init	
Initialize method
Crud.beforeSave	
Access and modify the data from the $request object
Crud.afterSave	
$id is only available if the save was successful
Crud.beforeFind	$subject->query	Modify the $queryData array and return it
Crud.recordNotFound	
If beforeFind could not find a record
Crud.afterFind	
Modify the record found by find() and return it
Crud.beforeRender	
Invoked right before the view will be rendered
view()
Event	Subject modifiers	Description
Crud.init	
Initialize method
Crud.beforeFind	$subject->query	Modify the $queryData array and return it
Crud.recordNotFound	
If beforeFind could not find a record
Crud.afterFind	$subject->item	Modify the record found by find() and return it
Crud.beforeRender	
Invoked right before the view will be rendered
delete()
Event	Subject modifiers	Description
Crud.init	
Initialize method
Crud.beforeFind	$subject->query	Modify the $queryData array and return it
Crud.recordNotFound	
If beforeFind could not find a record
Crud.beforeDelete	
Stop the delete by redirecting away from the action
Crud.afterDelete	

Crud.beforeRender	
Invoked right before the view will be rendered
Subscribing to an event
I would recommend using the Event class if you need to subscribe to more than one event
Full event class
The event class can be anywhere, but the default is inside app/Plugin/<plugin>/Lib/Crud/Event

Make sure you have configured the class loader inside app/Plugin/<plugin>/Config/bootstrap.php 


nodes\Autoload::addPath(App::pluginPath('Api') . 'Lib' . DS); // Replace Api with your plugin name
Your Event class should look like this:

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
			// If admin_add redirect to parent
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


public function beforeFilter() {
    parent::beforeFilter();
    $this->getEventManager()->attach(new Crud\Event\Demo());
}
A lamba method
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(function(\CakeEvent $event) { debug($event->subject->query); }, 'Crud.beforePaginate');
	}
Method in your controller
	public function beforeFilter() {
		parent::beforeFilter();
		$this->getEventManager()->attach(array($this, 'demoEvent'), 'Crud.beforePaginate');
	}

	public function demoEvent(\CakeEvent $event) {
		debug($event->subject->query);
	}
