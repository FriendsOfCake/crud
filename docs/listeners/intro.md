---
title: Listeners - Intro
layout: default
---

# Listeners

Listeners are an easy way to extend Crud functionality

They can hook into all the Crud events emitted from CrudComponent or the CrudActions

Most of the functionality in Crud is build with listeners, keeping the CrudAction code lean and
simple, and Crud flexible

Naming convention for Listeners is "Camelize" e.g. `Api` and `RelatedModels` - just like CakePHP for
`Helpers`, `Components` and `Behaviors`

# API
The `CrudComponent` API for managing listeners

## listener()

Get a listener by its name

{% highlight php %}
<?php
public function beforeFilter() {
	$listener = $this->Crud->listener('Api');
	$listener = $this->Crud->listener('Translations');
	$listener = $this->Crud->listener('RelatedModels');
}
?>
{% endhighlight %}

## addListener()

Add a Crud listener on the fly

{% highlight php %}
<?php
public function beforeFilter() {
	// Shorthand to add a listener in the Crud plugin
	$this->Crud->addListener('Api');

	// Add a listener in a specific plugin with a specific class name
	$this->Crud->addListener('Api', 'Crud.Api');

	// Add a listener in a specific plugin with a specific class name and provide default configuration
	$this->Crud->addListener('Api', 'Crud.Api', array('some' => 'defaults'));

	// Add a listener from Crud and provide default configuration
	$this->Crud->addListener('Api', null, array('some' => 'defaults'));
}
?>
{% endhighlight %}

## removeListener()

Remove a listener, and detach it from the CakePHP event manager (if attached)

{% highlight php %}
<?php
public function beforeFilter() {
	$this->Crud->removeListener('Api');
}
?>
{% endhighlight %}
