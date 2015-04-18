<table>
<thead>
	<tr>
		<th>Id</th>
		<th>Name</th>
		<th>Active</th>
	</tr>
</thead>
<tbody>
	<?php foreach (${$viewVar} as $blog) : ?>

		<tr>
			<td><?= $blog->id; ?></td>
			<td><?= $blog->name; ?></td>
			<td><?= $blog->is_active ? 'yes' : 'no'; ?></td>
		</tr>

		<?php endforeach; ?>
	</tbody>
</table>

<ul>
	<?= $this->Paginator->prev('PREV'); ?>

	<?= $this->Paginator->numbers(); ?>

	<?= $this->Paginator->next('NEXT'); ?>

</ul>

<?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} records out of {{count}} total.'); ?>
