<?php $this->start('pageTitle'); ?>
<h2><?php echo $pluralHumanName; ?></h2>
<?php $this->end(); ?>

<div class="<?php echo $pluralVar; ?> index scaffold-view">
<?php if (!empty($scaffoldFilters)) : ?>
  <div class="filters-container">
    <div class="btn-filter-container">
      <button type="button" class="btn btn-link btn-activate-filters filters-collapsed" data-toggle="collapse" data-target=".filters">
        <?php echo __d('crud', 'Filter results'); ?>
      </button>
    </div>
    <div class="filters collapse">
    <?php
      echo $this->Form->create($modelClass, array(
        'url' => array_merge(array('action' => $this->request->params['action']), $this->params['pass']),
      ));
      foreach ($scaffoldFilters as $_field => $scaffoldFilter) {
        echo $this->Form->input($_field, $scaffoldFilter);
      }
      echo $this->Form->submit(__d('crud', 'Filter'), array('name' => '_filter', 'class' => 'btn btn-primary btn-filter'));
      echo $this->Form->end();
    ?>
    </div>
  </div>
<?php endif; ?>
<table cellpadding="0" cellspacing="0">
  <thead>
    <tr>
    <?php foreach ($scaffoldFields as $_field => $_options): ?>
      <th><?php echo $this->Paginator->sort($_field); ?></th>
    <?php endforeach; ?>
      <th><?php echo __d('crud', 'Actions'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach (${$pluralVar} as ${$singularVar}):
      echo '<tr>';
        foreach ($scaffoldFields as $_field => $_options) {
          $isKey = false;
          if (!empty($associations['belongsTo'])) {
            foreach ($associations['belongsTo'] as $_alias => $_details) {
              if ($_field === $_details['foreignKey']) {
                $isKey = true;
                echo '<td>' . $this->Html->link(${$singularVar}[$_alias][$_details['displayField']], array('controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']])) . '</td>';
                break;
              }
            }
          }
          if ($isKey !== true) {
            $type = Hash::get($modelSchema, "{$_field}.type");
            if ($type == 'boolean') {
              echo '<td>' . (!!${$singularVar}[$modelClass][$_field] ? 'Yes' : 'No') . '</td>';
            } elseif (in_array($type, array('datetime', 'date', 'timestamp'))) {
              echo '<td>' . $this->Time->timeAgoInWords(${$singularVar}[$modelClass][$_field]) . '</td>';
            } elseif ($type == 'time') {
              echo '<td>' . $this->Time->nice(${$singularVar}[$modelClass][$_field]) . '</td>';
            } else {
              echo '<td>' . h(${$singularVar}[$modelClass][$_field]) . '</td>';
            }
          }
        }
      ?>
      <td class="actions">
        <div class="btn-group">
          <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            Actions
            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><?php echo $this->Html->link(__d('crud', 'View'), array('action' => 'view', ${$singularVar}[$modelClass][$primaryKey])); ?></li>
            <li><?php echo $this->Html->link(__d('crud', 'Edit'), array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey])); ?></li>
            <li><?php echo $this->Html->link(__d('crud', 'Delete'),array('action' => 'delete', ${$singularVar}[$modelClass][$primaryKey])); ?></li>
          </ul>
        </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
  <div class="paging paging-centered">
    <div>
      <?php
        echo $this->Paginator->prev(__d('crud', '<<'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__d('crud', '>>'), array(), null, array('class' => 'next disabled'));
      ?>
    </div>
    <p><?php
    echo $this->Paginator->counter(array(
      'format' => __d('crud', 'Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
    ));
    ?></p>
  </div>
</div>
