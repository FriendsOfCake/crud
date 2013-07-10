<h2><?= __d('crud', 'Crud Action config'); ?></h2>
<?php
if (!empty($CRUD_action_config)) {
	echo $this->Toolbar->makeNeatArray($CRUD_action_config);
} else {
	echo '<p><strong>Current action is not handled by Crud</strong></p>';
}
?>

<h2><?= __d('crud', 'Crud Listeners config'); ?></h2>
<?= $this->Toolbar->makeNeatArray($CRUD_listener_config); ?>

<h2><?= __d('crud', 'Crud Component config'); ?></h2>
<?= $this->Toolbar->makeNeatArray($CRUD_config); ?>
