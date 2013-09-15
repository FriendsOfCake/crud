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
  <?php echo $this->element('crud_view_related')?>
</div>
