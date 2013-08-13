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

  $_output = $this->Crud->relation($_field, ${$singularVar}, $associations);
  if ($_output) {
    echo "\t\t<dt>" . Inflector::humanize($_output['alias']) . "</dt>\n";
    echo "\t\t<dd>\n\t\t\t";
    echo $_output['output'];
    echo "\n\t\t&nbsp;</dd>\n";
  } else {
    echo "\t\t<dt>" . Inflector::humanize($_field) . "</dt>\n";
    echo "\t\t<dd>";
    echo $this->Crud->format(
      $_field,
      Hash::get(${$singularVar}, "{$modelClass}.{$_field}"),
      ${$singularVar},
      $modelSchema,
      $associations
    );
    echo "&nbsp;</dd>\n";
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
          echo "\t\t<dd>";
          echo $this->Crud->format($_field, Hash::get(${$singularVar}, "{$_alias}.{$_field}"), ${$singularVar});
          echo "&nbsp;</dd>\n";
        }
      ?>
      </dl>
    <?php endif; ?>
    <div class="actions">
      <ul>
      <li><?php
        echo $this->Html->link(
          __d('crud', 'View %s', Inflector::humanize(Inflector::underscore($_alias))),
          array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']])
        );
        echo "</li>\n";
        ?>
      </ul>
    </div>
  </div>
  <?php
  endforeach;
endif;

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
            __d('crud', "Add %s", Inflector::humanize(Inflector::underscore($_alias))),
            array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'add')
          ); ?> </li>
        </ul>
      </div>
    </div>
  <?php endforeach; ?>
</div>
