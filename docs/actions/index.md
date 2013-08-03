---
title: Actions - Index
layout: default
---

# Index CrudAction

The `index` CrudAction [paginates](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html) over the primary model in the controller.

Relevant links:
	[PHP source code]({{ site.github_url }}/Controller/Crud/Action/IndexCrudAction.php)
	|
	[API documentation](http://cakephp.dk/cakephp-crud/develop/class-IndexCrudAction.html)

# Events

{% include actions/events.md %}

<table class="table">
<thead>
	<tr>
		<th>Event</th>
		<th>Subject properties</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/event/startup.md %}
	{% include actions/event/initialize.md %}
	{% include actions/event/before_paginate.md %}
	{% include actions/event/after_paginate.md %}
	{% include actions/event/before_render.md %}
</tbody>
</table>

# Configuration

{% include actions/configuration.md %}

<table class="table">
<thead>
	<tr>
		<th>Key</th>
		<th>Default value</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/config/enabled.md %}
	{% include actions/config/find_method.md %}
	{% include actions/config/view.md %}
	{% include actions/config/view_var.md %}
	{% include actions/config/serialize.md %}
</tbody>
</table>

# Methods

This is a list of the most relevant public methods in the Crud action class.

For a full list please see the [full API documentation]({{site.api_url}}/class-AddCrudAction.html)

<table class="table">
<thead>
	<tr>
		<th>Method</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/method/view.md %}
	{% include actions/method/view_var.md %}
	{% include actions/method/find_method.md %}
</tbody>
</table>

# Query string parameters

You can easily add query string pagination to your Api `index` actions by adding
Api Pagination and enabling query strings. This will give api-requesters the the possibility
to create custom data collections using GET parameters in the URL
(e.g. `http://example.com/controller.{format}?key=value`)

The following query string pagination parameters will automatically become available:

- **limit**: an integer limiting the number of results
- **sort**: the string value of a fieldname to sort the results by
- **direction**: either `asc` or `desc` (only works in combination with the `sort` parameter)
- **page**: an integer pointing to a specific data collection page

[Please also see the CakePHP documentation on Pagination](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html)

[Please also see the CakePHP documentation on out of range `page` requests](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html#out-of-range-page-requests)

Setup by enabling Api Pagination as described [here]({{site.url}}/docs/listeners/api-pagination.html#setup)

Then enable query string pagination by adding this to your `/Controller/AppController.php` file

{% highlight php %}
<?php
  public $paginate = array(
    'paramType' => 'querystring'
  );
?>
{% endhighlight %}
