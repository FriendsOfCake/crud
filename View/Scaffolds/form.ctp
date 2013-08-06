<div class="<?php echo $pluralVar; ?> form">
  <?php echo $this->Form->create(); ?>
    <h2><?php echo Inflector::humanize($this->request->action) . ' ' .  $singularHumanName; ?></h2>
    <?php echo $this->element('redirect_url'); ?>
    <?php echo $this->Form->inputs($scaffoldFields, null, array(
      'legend' => false
    )); ?>
    <div class="submit">
    <?php
      echo $this->Form->submit(__d('crud', 'Save'), array('name' => '_save', 'div' => false));
      echo "&nbsp;";
      echo $this->Form->submit(__d('crud', 'Save and continue editing'), array('name' => '_edit', 'div' => false));
      echo "&nbsp;";
      echo $this->Form->submit(__d('crud', 'Save and add another'), array('name' => '_add', 'div' => false));
      echo "&nbsp;";
    ?>
    </div>
  <?php echo $this->Form->end(); ?>
  <?php
    if ($this->request->action !== 'add') {
      echo $this->Form->postLink(__d('crud', 'Delete %s', $singularHumanName), array('action' => 'delete', $this->data[$modelClass][$primaryKey]), null, __d('crud', 'Are you sure you want to delete # %s?', $this->data[$modelClass][$primaryKey]));
    }
  ?>
</div>

<?php echo $this->element('sidebar_actions'); ?>
