---
title: Installation
layout: default
---

# Requirements

* CakePHP 2.2
* PHP 5.3

# Installing

### composer

The recommended installation method for this plugin is by using composer. Just add this to your
`composer.json` configuration:

{% highlight json %}
{
	"require" : {
		"FriendsOfCake/crud": "3.*"
	}
}
{% endhighlight %}

### git clone

Alternatively you can just `git clone` the code into your application

```
git clone git://github.com/FriendsOfCake/crud.git app/Plugin/Crud
```

### git submodule

Or add it as a git module, this is recommended over `git clone` since it's easier to keep up to date
with development that way

```
git submodule add git://github.com/FriendsOfCake/crud.git app/Plugin/Crud
```

# Loading and installation

Add the following to your `app/Config/bootstrap.php`

{% highlight php %}
<?php
CakePlugin::load('Crud');
?>
{% endhighlight %}

In your `AppController` do **one** of the following to inject the required code for `CrudComponent`
to work

## >= PHP 5.4

Add an App::uses in the top of your `AppController.php` file

{% highlight php %}
<?php
App::uses('CrudControllerTrait', 'Crud.Lib');
?>
{% endhighlight %}

and add the `CrudControllerTrait` inside your `AppController` class

{% highlight php %}
<?php
class AppController extends Controller {

	use CrudControllerTrait;

	public $components = array(
		'Crud.Crud' => array(
			'actions' => array(
				'index', 'add', 'edit', 'view', 'delete'
			)
		)
	);

}
?>
{% endhighlight %}

## <= PHP 5.3

{% highlight php %}
<?php
/**
 * Application wide controller
 */
class AppController extends Controller {

	public $components = array(
		'Crud.Crud' => array(
			'actions' => array(
				'index', 'add', 'edit', 'view', 'delete'
			)
		)
	);
	
/**
 * List of components which can handle action invocation
 * @var array
 */
	public $dispatchComponents = array();

/**
 * Dispatches the controller action. Checks that the action exists and isn't private.
 *
 * If CakePHP raises MissingActionException we attempt to execute Crud
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

					// Skip if execute isn't defined in the Component
					if (!method_exists($this->{$component}, 'execute')) {
						continue;
					}

					// Execute the callback, can return CakeResponse object
					return $this->{$component}->execute();
				}
			}

			// No additional callbacks, re-throw the normal Cake exception
			throw $e;
		}
	}
}
?>
{% endhighlight %}
