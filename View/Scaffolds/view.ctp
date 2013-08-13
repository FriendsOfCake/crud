<?php $this->start('pageTitle'); ?>
<h2><?php echo $scaffoldPageTitle; ?></h2>
<?php $this->end(); ?>

<div class="<?php echo $pluralVar; ?> view scaffold-view">
  <dl>
<?php
$i = 0;
foreach ($scaffoldFieldsData as $_field => $_options) {
  if (in_array($_field, array($primaryKey, $displayField))) {
    continue;
  }

  $isKey = false;
  if (!empty($associations['belongsTo'])) {
    foreach ($associations['belongsTo'] as $_alias => $_details) {
      if ($_field === $_details['foreignKey']) {
        $isKey = true;
        echo "\t\t<dt>" . Inflector::humanize($_alias) . "</dt>\n";
        echo "\t\t<dd>\n\t\t\t";
        echo $this->Html->link(
          ${$singularVar}[$_alias][$_details['displayField']],
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']])
        );
        echo "\n\t\t&nbsp;</dd>\n";
        break;
      }
    }
  }
  if ($isKey !== true) {
    echo "\t\t<dt>" . Inflector::humanize($_field) . "</dt>\n";
    $type = Hash::get($modelSchema, "{$_field}.type");
    if ($type == 'boolean') {
      echo "\t\t<dd>" . (!!${$singularVar}[$modelClass][$_field] ? 'Yes' : 'No') . "&nbsp;</dd>\n";
    } elseif (in_array($type, array('datetime', 'date', 'timestamp'))) {
      echo "\t\t<dd>" . $this->Time->timeAgoInWords(${$singularVar}[$modelClass][$_field]) . "&nbsp;</dd>\n";
    } elseif ($type == 'time') {
      echo "\t\t<dd>" . $this->Time->nice(${$singularVar}[$modelClass][$_field]) . "&nbsp;</dd>\n";
    } else {
      echo "\t\t<dd>" . h(${$singularVar}[$modelClass][$_field]) . "&nbsp;</dd>\n";
    }
  }
}
?>
  </dl>
  <?php
  if (!empty($associations['hasOne'])) :
  foreach ($associations['hasOne'] as $_alias => $_details): ?>
  <div class="related">
    <h3><?php echo __d('crud', "Related %s", Inflector::humanize($_details['controller'])); ?></h3>
  <?php if (!empty(${$singularVar}[$_alias])): ?>
    <dl>
  <?php
      $i = 0;
      $otherFields = array_keys(${$singularVar}[$_alias]);
      foreach ($otherFields as $_field) {
        echo "\t\t<dt>" . Inflector::humanize($_field) . "</dt>\n";
        echo "\t\t<dd>\n\t" . ${$singularVar}[$_alias][$_field] . "\n&nbsp;</dd>\n";
      }
  ?>
    </dl>
  <?php endif; ?>
    <div class="actions">
      <ul>
      <li><?php
        echo $this->Html->link(
          __d('crud', 'Edit %s', Inflector::humanize(Inflector::underscore($_alias))),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'edit', ${$singularVar}[$_alias][$_details['primaryKey']])
        );
        echo "</li>\n";
        ?>
      </ul>
    </div>
  </div>
  <?php
  endforeach;
  endif;

  if (empty($associations['hasMany'])) {
    $associations['hasMany'] = array();
  }
  if (empty($associations['hasAndBelongsToMany'])) {
    $associations['hasAndBelongsToMany'] = array();
  }
  $relations = array_merge($associations['hasMany'], $associations['hasAndBelongsToMany']);
  $i = 0;
  foreach ($relations as $_alias => $_details):
  $otherSingularVar = Inflector::variable($_alias);
  ?>
  <div class="related">
    <h3><?php echo __d('crud', "Related %s", Inflector::humanize($_details['controller'])); ?></h3>
  <?php if (!empty(${$singularVar}[$_alias])): ?>
    <table cellpadding="0" cellspacing="0">
      <thead>
        <tr>
      <?php
          $otherFields = array_keys(${$singularVar}[$_alias][0]);
          if (isset($_details['with'])) {
            $index = array_search($_details['with'], $otherFields);
            unset($otherFields[$index]);
          }
          foreach ($otherFields as $_field) {
            echo "\t\t<th>" . Inflector::humanize($_field) . "</th>\n";
          }
      ?>
          <th class="actions">Actions</th>
        </tr>
      </thead>
      <tbody>
  <?php
      $i = 0;
      foreach (${$singularVar}[$_alias] as ${$otherSingularVar}):
        echo "\t\t<tr>\n";

        foreach ($otherFields as $_field) {
          echo "\t\t\t<td>" . ${$otherSingularVar}[$_field] . "</td>\n";
        }

        echo "\t\t\t<td class=\"actions\">\n";
        echo "\t\t\t\t";
        echo $this->Html->link(
          __d('crud', 'View'),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'view', ${$otherSingularVar}[$_details['primaryKey']])
        );
        echo "\n";
        echo "\t\t\t\t";
        echo $this->Html->link(
          __d('crud', 'Edit'),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'edit', ${$otherSingularVar}[$_details['primaryKey']])
        );
        echo "\n";
        echo "\t\t\t\t";
        echo $this->Html->link(
          __d('crud', 'Delete'),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'delete', ${$otherSingularVar}[$_details['primaryKey']])
        );
        echo "\n";
        echo "\t\t\t</td>\n";
      echo "\t\t</tr>\n";
      endforeach;
  ?>
      </tbody>
    </table>
  <?php endif; ?>
    <div class="actions">
      <ul>
        <li><?php echo $this->Html->link(
          __d('crud', "New %s", Inflector::humanize(Inflector::underscore($_alias))),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'add')
        ); ?> </li>
      </ul>
    </div>
  </div>
  <?php endforeach; ?>
</div>
