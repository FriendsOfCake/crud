<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Scaffolds
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<div class="<?php echo $pluralVar; ?> form">
<?php
  if (empty($scaffoldFieldExclude)) {
    $scaffoldFieldExclude = array('created', 'modified', 'updated');
  }
  echo $this->Form->create();
  echo $this->Form->inputs($scaffoldFields, $scaffoldFieldExclude);
  ?>
  <div class="submit">
  <?php
    echo $this->Form->submit(__d('cake', 'Save'), array('name' => '_save', 'div' => false));
    echo "&nbsp;";
    echo $this->Form->submit(__d('cake', 'Save and continue editing'), array('name' => '_edit', 'div' => false));
    echo "&nbsp;";
    echo $this->Form->submit(__d('cake', 'Save and add another'), array('name' => '_add', 'div' => false));
    echo "&nbsp;";
  ?>
  </div>
  <?php echo $this->Form->end(); ?>
  <?php
    if ($this->request->action !== 'add') {
      echo $this->Form->postLink(__d('cake', 'Delete %s', $singularHumanName), array('action' => 'delete', $this->data[$modelClass][$primaryKey]), null, __d('cake', 'Are you sure you want to delete # %s?', $this->data[$modelClass][$primaryKey]));
    }
  ?>
</div>

<?php echo $this->element('sidebar_actions'); ?>
