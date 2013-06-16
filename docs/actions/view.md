---
title: Actions - View
layout: default
---

# View CrudAction

The `view` CrudAction will read a record from the database based on the ID that is part of the request.

The source code can be found here: [Controller/Crud/Action/ViewCrudAction.php]({{ site.github_url }}/Controller/Crud/Action/ViewCrudAction.php)

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
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as the $queryParams in a behaviors beforeFind() or 2nd argument to any Model::find()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record this event is emitted</td>
	</tr>
	<tr>
		<td>Crud.afterFind</td>
		<td>
			$subject->id
			$subject->item
		</td>
		<td>Modify the $subject->item property if you need to do any afterFind() operations</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>
