<h2><?= __d('crud', 'Config'); ?></h2>

<?php

if (!empty($CRUD_action_config)) {
	$action = $CRUD_action_config;
} else {
	$action = __('crud', 'Current action is not handled by Crud');
}
$component = $CRUD_config;
$listeners = $CRUD_listener_config;

$config = array(
	__d('crud', 'Action') => $action,
	__d('crud', 'Component') => $component,
	__d('crud', 'Listeners') => $listeners,
);

echo $this->Toolbar->makeNeatArray($config);
?>

<h2><?= __d('crud', 'Events triggered'); ?></h2>
<?= $this->Toolbar->makeNeatArray($CRUD_events); ?>
