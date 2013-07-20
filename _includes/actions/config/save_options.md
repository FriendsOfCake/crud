<tr>
	<td><code>saveOptions</code></td>
	<td>
		<code>
		[<br />
			&nbsp;&nbsp;'validate' => 'first',<br />
			&nbsp;&nbsp;'atomic' => true<br />
		]
		</code>
	</td>
	<td>
		The 2nd parameter to <code>Model::saveAll</code>.
		<br />
		<br />
		By default validation will be done first, and the save will be atomic by wrapping it all in a transaction.
		<br />
		<br />
		Please see the <a href="http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-saveall-array-data-null-array-options-array">CakePHP saveAll documentation</a> for more information.
	</td>
</tr>
