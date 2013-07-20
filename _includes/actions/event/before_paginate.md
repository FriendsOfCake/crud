<tr>
	<td>Crud.beforePaginate</td>
	<td><code>paginator</code></td>
	<td>
			Triggered before <code>Controller::paginate()</code> is called.
			<br />
			The <code>paginator</code> property is a reference to the <code>PaginatorComponent</code>.
			<br />
			If you wish to modify the pagination settings, you should <strong>only</strong> modify <code>$event->subject->paginator->settings</code>.
			<br />
			Modifying <code>Controller::$paginate</code> will not have any effect during this callback.
	</td>
</tr>
