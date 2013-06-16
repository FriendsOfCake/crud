---
title: Actions - Index
layout: default
---

# Index CrudAction

The `index` CrudAction paginates over the primary model in the controller.

The source code can be found here: [Controller/Crud/Action/IndexCrudAction.php]({{ site.github_url }}/Controller/Crud/Action/IndexCrudAction.php)

# Events

<table class="table">
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
		<td>Executed before the pagination call is made. You can modify the pagination array like you normally would in your model inside this event</td>
	</tr>
	<tr>
		<td>Crud.afterPaginate</td>
		<td>$subject->items</td>
		<td>Executed after the pagination call is made. You can modify the items from the database here.</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>
