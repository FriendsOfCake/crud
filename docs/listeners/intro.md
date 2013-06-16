---
title: Listeners - Intro
layout: default
---

# Listeners

Listeners are an easy way to extend Crud functionality

They can hook into all the Crud events emitted from CrudComponent or the CrudActions

Most of the functionality in Crud is build with listeners, keeping the CrudAction code lean and simple, and Crud flexible

# API

The `CrudComponent` API for managing listeners

## listener()

Get a listener by its name

{% highlight php %}
<?php
public function beforeFilter() {
	$listener = $this->Crud->listener('api');
	$listener = $this->Crud->listener('translations');
	$listener = $this->Crud->listener('relatedModels');
}
?>
{% endhighlight %}

## addListener()

Add a Crud listener on the fly

{% highlight php %}
<?php
public function beforeFilter() {
	$this->Crud->addListener('api', 'Crud.Api');
	$this->Crud->addListener('api', 'Crud.Api', array('some' => 'defaults'));
}
?>
{% endhighlight %}

## removeListener()

Remove a listener, and detach it from the CakePHP event manager (if attached)

{% highlight php %}
<?php
public function beforeFilter() {
	$this->Crud->removeListener('api');
}
?>
{% endhighlight %}
