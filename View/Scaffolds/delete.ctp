<?php
  $primaryKeyValue = $this->Form->value("{$modelClass}.{$primaryKey}");
  if (empty($primaryKeyValue) && !empty(${$singularVar})) {
    $primaryKeyValue =  ${$singularVar}[$modelClass][$primaryKey];
  }
?>
<div class="<?php echo $pluralVar; ?> form">
  <?php echo $this->Form->create(null, array('type' => 'delete')); ?>
    <?php echo $this->element('redirect_url'); ?>
    <h2><?php echo Inflector::humanize($this->request->action) . ' ' .  $singularHumanName; ?></h2>
    <p><?php echo  __d('cake', 'Are you sure you want to delete # %s?', $primaryKeyValue); ?></p>
  <?php echo $this->Form->end(__d('crud', 'Delete')); ?>
</div>

<?php echo $this->element('sidebar_actions'); ?>
