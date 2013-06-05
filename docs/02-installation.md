# Requirements

* CakePHP 2.1
* PHP 5.3

# Installing

### composer

The only installation method supported by this plugin is by using composer. Just add this to your composer.json configuration:

```
{
	"extra": {
		"installer-paths": {
			"app/Plugin/Crud": ["jippi/crud"]
		}
	},
	"require" : {
		"jippi/crud": "master"
	}
}
```

### git clone

```
git clone git://github.com/Jippi/cakephp-crud.git app/Plugin/Crud
```

### git submodule

```
git submodule add git://github.com/Jippi/cakephp-crud.git app/Plugin/Crud
```

# Loading and installation

Add the following to your __app/Config/bootstrap.php__

```php
CakePlugin::load('Crud');
```

In your `AppController` do **one** of the following to inject the required code for `CrudComponent` to work

## >= PHP 5.4

Add an App::uses in the top of you `AppController.php` file

```php
App::uses('CrudControllerTrait', 'Crud.Lib');
```

and add the `CrudControllerTrait` inside you `AppController` class

```php
class AppController extends Controller {

	use CrudControllerTrait;

}
```

## <= PHP 5.3

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

		public $dispatchComponents = array();

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
```
