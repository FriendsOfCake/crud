<div class="btn-group">
	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
		<?= __d('crud', 'Actions'); ?>
		<span class="caret"></span>
	</a>

	<ul class="dropdown-menu">
		<?php
		foreach ($scaffoldControllerActions['record'] as $_action) {
			echo "\t\t<li>";
			echo $this->Html->link(
				sprintf('%s %s', Inflector::humanize($_action), $singularHumanName),
				array('action' => $_action, $_singularVar[$modelClass][$primaryKey])
			);
			echo " </li>\n";
		}
		?>
	</ul>
</div>
