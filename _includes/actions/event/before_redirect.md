<tr>
	<td>Crud.beforeRedirect</td>
	<td>
		<code>url</code><br />
		<code>status</code><br />
		<code>exit</code>
	</td>
	<td>
		<code>url</code> A string or Router::url() compatible array<br />
		<code>status</code> A redirect status code<br />
		<code>exit</code> Should <code>exit();</code> be called after redirect?<br />

		Invoked right before a call to <code>Controller::redirect()</code> is made.
		<br />
		This is also before the controllers own beforeRedirect callback
	</td>
</tr>
