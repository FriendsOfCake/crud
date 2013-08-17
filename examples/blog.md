---
title: Examples - Blog
layout: default
---

# Introduction

This is a Crud version of the cakephp blog tutorial [part one](http://book.cakephp.org/2.0/en/tutorials-and-examples/blog/blog.html) and [part two](http://book.cakephp.org/2.0/en/tutorials-and-examples/blog/part-two.html)

It's meant to show that everything the example does in Controller is done automatically by Crud, and that it even provides a more flexible and robust solution.

This example is not meant to be a introduction to cakephp, but more an easy way to compare bake & hand-crafted code vs. crud.

This is obviously a very simple example, and doesn't showcase all the features of Crud.

Please dive into the [documentation]({{site.url}}/docs/) for more information.

# Installation & Configuration

Please follow the [installation guidelines]({{ site.url }}/docs/installation.html) on how to get the Crud plugin

Add the following to your `AppController.php` (php5.4) or follow the [installation guidelines]({{site.url}}/docs/installation.html) for alternative installation methods.

{% highlight php %}
<?php
App::uses('Controller', 'Controller');
App::uses('CrudControllerTrait', 'Crud.Lib');

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

# Basic tutorial

This part covers the basic introductional blog example, nothing more, nothing less

## Initialize the database

Create a table with some sample data with the following sql:

{% highlight sql %}
CREATE TABLE posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50),
    body TEXT,
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

/* Then insert some posts for testing: */
INSERT INTO posts (title,body,created)
    VALUES ('The title', 'This is the post body.', NOW());
INSERT INTO posts (title,body,created)
    VALUES ('A title once again', 'And the post body follows.', NOW());
INSERT INTO posts (title,body,created)
    VALUES ('Title strikes back', 'This is really exciting! Not.', NOW());
{% endhighlight %}

## Post Model

Create `app/Model/Post.php`

{% highlight php %}
<?php
class Post extends AppModel {
    public $validate = array(
        'title' => array(
            'rule' => 'notEmpty'
        ),
        'body' => array(
            'rule' => 'notEmpty'
        )
    );
}
?>
{% endhighlight %}

## Post Controller

Create `app/Controller/PostsController.php`

{% highlight php %}
<?php

App::uses('AppController', 'Controller');

class PostsController extends AppController {

}
?>
{% endhighlight %}

## Post Views

Since Crud is bake compatible, we can just bake the views:

`Console/cake bake view Post --public`

## Done

That was it.. the 15 minute blog tutorial done with Crud.

Obviously there isn't a huge win in this small example, but read on and see how much awesome features Crud can provide.

Read on and lets add a API layer on top of our new Blog.

# Adding API

Since our new blog is super webscale, and we want people to be able to consume our content through a nice API we want to add some JSON and XML response formats.

## Route configuration

We need to tell CakePHP router that we want to [handle JSON and XML extensions](http://book.cakephp.org/2.0/en/development/routing.html#file-extensions)

{% highlight php %}
<?php
Router::parseExtensions('json', 'xml');
?>
{% endhighlight %}

## Modify AppController

We need to attach the [cakephp request handler](http://book.cakephp.org/2.0/en/core-libraries/components/request-handling.html) and the [crud api listener]({{site.url}}/docs/listeners/api.html) by modifying the `AppController::$components` array

{% highlight php %}
<?php
public $components = array(
	'RequestHandler', // <- new
	'Crud.Crud' => array(
	 	'actions' => array(
			'index', 'add', 'edit', 'view', 'delete'
		),
		'listeners' => array( // <- new
			'api' => 'Crud.Api'
		)
	)
);
?>
{% endhighlight %}

And thats its.. we now got a nice REST api for our posts controller, or any other controller your application could have.

## Using the new API

Reading posts in XML or JSON is as easy as using http://your-site-domain.com/posts.xml for XML or http://your-site-domain.com/posts.json for JSON

### JSON API

By adding '.json' to your URLs CakePHP and Crud will make sure that all responses is in JSON

#### List posts

Simply request `/posts.json` and you will get a list of posts in JSON format.

{% highlight text %}
curl -I -X GET http://your-site-domain.com/posts.json
{% endhighlight %}

#### Add post

Simply POST to `/posts/add.json`

{% highlight text %}
curl -I -X POST http://your-site-domain.com/posts/add.json \
	-d title="My new JSON API blog post" \
	-d body="With an epic body"

HTTP/1.1 201 Created
Server: nginx/1.4.1
Date: Sun, 16 Jun 2013 12:17:12 GMT
Content-Type: application/json; charset=UTF-8
Content-Length: 43
Connection: keep-alive
Set-Cookie: CAKEPHP=qp2dpvkpkqqk81mdn3iajdals2; expires=Sun, 16-Jun-2013 16:17:12 GMT; path=/; HttpOnly
Location: http://crud.local.bownty.net/posts/view/5

{
  "success": true,
  "data": {
    "Post": {
      "id": "5"
    }
  }
}
{% endhighlight %}

#### Validation errors

Example on how validation errors in JSON looks

In this example, I left out the `body` field

{% highlight text %}
curl -I -X POST http://your-site-domain.com/posts/add.json \
	-d title="My new JSON API blog post"

HTTP/1.1 400 Bad Request
Server: nginx/1.4.1
Date: Sun, 16 Jun 2013 12:25:12 GMT
Content-Type: application/json; charset=UTF-8
Content-Length: 69
Connection: keep-alive
Set-Cookie: CAKEPHP=vevclsl2o6r7h4v7uon9j5tkd1; expires=Sun, 16-Jun-2013 16:25:12 GMT; path=/; HttpOnly

{
  "success": false,
  "data": {
    "body": ["This field cannot be left blank"]
  }
}
{% endhighlight %}

### XML API

By adding '.xml' to your URLs CakePHP and Crud will make sure that all responses is in XML

#### List posts

Simply request `/posts.xml` and you will get a list of posts in XML format.

{% highlight text %}
curl -I -X GET http://your-site-domain.com/posts.xml
{% endhighlight %}

#### Add post

Simply POST to `/posts/add.xml`

{% highlight text %}
curl -I -X POST http://your-site-domain.com/posts/add.xml \
	-d title="My new XML API blog post" \
	-d body="With an epic body"

HTTP/1.1 201 Created
Server: nginx/1.4.1
Date: Sun, 16 Jun 2013 12:17:12 GMT
Content-Type: application/json; charset=UTF-8
Content-Length: 43
Connection: keep-alive
Set-Cookie: CAKEPHP=qp2dpvkpkqqk81mdn3iajdals2; expires=Sun, 16-Jun-2013 16:17:12 GMT; path=/; HttpOnly
Location: http://crud.local.bownty.net/posts/view/5

<?xml version="1.0" encoding="UTF-8"?>
<response>
  <success>1</success>
  <data>
    <Post>
      <id>6</id>
    </Post>
  </data>
</response>
{% endhighlight %}

#### Validation errors

Example on how validation errors in XML looks

In this example, I left out the `body` field

{% highlight text %}
curl -I -X POST http://your-site-domain.com/posts/add.xml \
	-d title="My new XML API blog post" \

HTTP/1.1 400 Bad Request
Server: nginx/1.4.1
Date: Sun, 16 Jun 2013 12:27:44 GMT
Content-Type: application/xml; charset=UTF-8
Content-Length: 138
Connection: keep-alive
Set-Cookie: CAKEPHP=3i5p02o6ove6dhohmb11acq765; expires=Sun, 16-Jun-2013 16:27:44 GMT; path=/; HttpOnly

<?xml version="1.0" encoding="UTF-8"?>
<response>
	<success>0</success>
	<data>
		<body>This field cannot be left blank</body>
	</data>
</response>
{% endhighlight %}
