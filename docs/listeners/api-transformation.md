---
title: Api transformation
layout: default
---

# Api Transformation Listener

The default API output is similar to CakePHP arrays. This is fine for private
APIs, but for public APIs that format isn't consitent with other public APIs
such as Google, Twitter and GitHub. This listener allows you to transform the
API output. See the example below.

__Default output__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "Comment": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP"
        },
        "Comment": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

__Transformed output__

{% highlight json %}
{
    "success": true,
    "data": [{
        "id": 15,
        "username": "FriendsOfCake",
        "comments": [{
            "id": 51,
            "message": "Hello."
        }]
    }, {
        "id": 16,
        "username": "CakePHP",
        "comments": [{
            "id": 324,
            "message": "I like FriendsOfCake."
        }, {
            "id": 849,
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}


# Setup

First make sure you have a _working_ API using the `ApiListener`. The
`ApiTransformationListener` uses the `ApiListener` to activate on API requests.

As with all listeners you can add this listener by using the `$components`
array:

{% highlight php %}
<?php
class SamplesController extends AppController {

  public $components = [
    'RequestHandler',
    'Crud.Crud' => [
      'actions' => ['index', 'view'],
      'listeners' => ['Api', 'ApiTransformation']
    ]
  ];

}
?>
{% endhighlight %}

It's also possible to add the listener on the fly:

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Crud->addListener('Api');
    $this->Crud->addListener('ApiListener');
  }

}
?>
{% endhighlight %}

# Configuration

The `ApiTransformationListener` takes several configuration options:

{% highlight php %}
<?php
class SamplesController extends AppController {

  public $components = [
    'RequestHandler',
    'Crud.Crud' => [
      'actions' => ['index', 'view'],
      'listeners' => [
        'Api',
        'ApiTransformation' => [

          // Remove top model and nest associations.
          'changeNesting' => true,

          // Make model keys lowercase and plural/singular.
          'changeKeys' => true,

          // Convert time strings to Unix time integers.
          'changeTime' => true,

          // Cast numeric strings to actual numbers.
          'castNumbers' => true,

          // Methods to run for every data key.
          'keyMethods' => [],

          // Methods to run for every data value.
          'valueMethods' => [],

          // Key-value pairs for 'changeKeys'.
          'replaceMap' => []
        ]
      ]
    ]
  ];

}
?>
{% endhighlight %}

Below is a detailed explanation of these options.

## changeNesting

The `changeNesting` option removes the top model name for every record and
nests all associated records into the primary record.

_Warning: When you have field names equal to the model names it will overwrite
your keys._

See below for an example:

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "Comment": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP"
        },
        "Comment": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

__After `changeNesting`__

{% highlight json %}
{
    "success": true,
    "data": [{
        "id": "15",
        "username": "FriendsOfCake",
        "Comment": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "id": "16",
        "username": "CakePHP",
        "Comment": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

## changeKeys

The `changeKeys` option replaces the model keys with their lowercase names. By
default it will derive these names from the model associations. `hasMany` and
`hasAndBelongsToMany` associations will get plural names while `belongsTo` and
`hasOne` will remain singular. The primary model will always be singular,
because there will only be one in every record. Example:

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "Comment": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP"
        },
        "Comment": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

__After `changeKeys`__

{% highlight json %}
{
    "success": true,
    "data": [{
        "user": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "comments": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "user": {
            "id": "16",
            "username": "CakePHP"
        },
        "comments": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

For flexibility you can also define your own `replaceMap`. This option will
then be used instead of the model associations. See further down on how to use
it.

## changeTime

The `changeTime` option changes dates and datetimes to Unix time integers.
Example:

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake",
            "created" : "2013-08-30 05:23:45"
        }
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP",
            "created" : "2013-08-30 18:12:33"
        }
    }]
}
{% endhighlight %}

__After `changeTime`__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake",
            "created" : 1377833025
        }
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP",
            "created" : 1377879153
        }
    }]
}
{% endhighlight %}

## castNumbers

The `castNumbers` options changes numeric strings to real numbers. Example:

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "balance": "274.04"
        }
    }, {
        "User": {
            "id": "16",
            "balance": "794.95"
        }
    }]
}
{% endhighlight %}

__After `castNumbers`__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": 15,
            "balance": 274.04
        }
    }, {
        "User": {
            "id": 16,
            "balance": 794.95
        }
    }]
}
{% endhighlight %}

## keyMethods & valueMethods

To avoid looping through everything multiple times, when you want to do your
own custom transformation the `ApiTransformationListener` allows you to hook
into the recursive loop method. This can be done by defining additional methods
in the `keyMethods` or `valueMethods` options. Their use is similar to the
`call_user_func()` syntax, the callback recieves both the value, and the path
(dot-delimited keys for the current array index) returning the updated value:

__Example methods__

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    parent::beforeFilter();

    $slug = function($value, $key) {
		if ($key === '0.User.username') {
        	return strtolower(Inflector::slug($value, '-'));
		}
		return $value;
    };

    $this->Crud->addListener('Api');
    $this->Crud->addListener('ApiListener', 'ApiListener', [
      'valueMethods' => [$slug]
    ]);
  }

}
?>
{% endhighlight %}

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "Friends Of Cake"
        }
    }]
}
{% endhighlight %}

__After the methods __

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "friends-of-cake"
        }
    }]
}
{% endhighlight %}

## replaceMap

As stated above you can override the `replaceKeys` map by setting the
`replaceMap` option:

__Example map__

{% highlight php %}
<?php
class SamplesController extends AppController {

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Crud->addListener('Api');
    $this->Crud->addListener('ApiListener', 'ApiListener', [
      'replaceMap' => [
        'User' => 'account',
        'Comment' => 'remarks'
      ]
    ]);
  }

}
?>
{% endhighlight %}

__Initial reponse__

{% highlight json %}
{
    "success": true,
    "data": [{
        "User": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "Comment": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "User": {
            "id": "16",
            "username": "CakePHP"
        },
        "Comment": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}

__After `changeKeys` with the custom `replaceMap`__

{% highlight json %}
{
    "success": true,
    "data": [{
        "account": {
            "id": "15",
            "username": "FriendsOfCake"
        },
        "remarks": [{
            "id": "51",
            "message": "Hello."
        }]
    }, {
        "account": {
            "id": "16",
            "username": "CakePHP"
        },
        "remarks": [{
            "id": "324",
            "message": "I like FriendsOfCake."
        }, {
            "id": "849",
            "message": "Cool."
        }]
    }]
}
{% endhighlight %}
