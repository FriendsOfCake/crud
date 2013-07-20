---
title: Messages
layout: default
---

# Flash and error messages

Flash and exceptions are defined on action classes or on the Crud component itself

The list of predefined messages is as follows:

<table class="table">
<thead>
	<tr>
		<th>Action</th>
		<th>Message key</th>
		<th>Message</th>
		<th>Error Code</th>
		<th>Exception Class</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>*</td>
		<td>invalidId</td>
		<td>Invalid id</td>
		<td>400</td>
		<td>BadRequestException</td>
	</tr>
	<tr>
		<td>*</td>
		<td>recordNotFound</td>
		<td>Not Found</td>
		<td>404</td>
		<td>NotFoundException</td>
	</tr>
	<tr>
		<td>*</td>
		<td>badRequestMethod</td>
		<td>Method not allowed. This action permits only {methods}</td>
		<td>405</td>
		<td>MethodNotAllowedException</td>
	</tr>
	<tr>
		<td>add</td>
		<td>success</td>
		<td>Successfully created {name}</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>add</td>
		<td>error</td>
		<td>Could not create {name}</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>delete</td>
		<td>success</td>
		<td>Successfully deleted {name}</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>delete</td>
		<td>error</td>
		<td>Could not delete {name}</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>edit</td>
		<td>success</td>
		<td>Successfully updated {name}</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>edit</td>
		<td>error</td>
		<td>Could not update {name}</td>
		<td></td>
		<td></td>
	</tr>
</tbody>
</table>

# Modifying existing messages

To Change the text or other parameters of a given message, it can be modified by changing the
config of the action class:

{% highlight php %}
<?php
class DemoController extends AppController {

	public function beforeFilter() {
		$this->Crud->action()->config('messages.success.class', 'message success');

		parent::beforeFilter();
	}

}
?>
{% endhighlight %}
