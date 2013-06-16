---
title: Actions - Add
layout: default
---

# Add CrudAction

The `add` CrudAction will create a record if the request is `HTTP POST` and the data is valid.

The source code can be found here: [Controller/Crud/Action/AddCrudAction.php]({{ site.github_url }}/Controller/Crud/Action/AddCrudAction.php)

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
		<td>Crud.beforeSave</td>
		<td>N/A</td>
		<td>Access and modify the data from the $request object like you normally would in your own controller action</td>
	</tr>
	<tr>
		<td>Crud.afterSave</td>
		<td>$subject->id</td>
		<td>`$id` is only available if the save was successful. You can test also test on the `$subject->success` property if the save worked.</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>
