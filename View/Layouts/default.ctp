<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <?php echo $this->Html->charset(); ?>
  <title><?php echo $title_for_layout; ?></title>

  <?php
    echo $this->Html->meta('icon');

    echo $this->Html->css('Crud.cake.generic');

    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');
  ?>
</head>
<body>
  <div id="container">
    <div id="header">
      <h1><?php echo $scaffoldTitle; ?> - <?php echo $title_for_layout; ?></h1>
    </div>
    <div id="content">

      <?php echo $this->Session->flash(); ?>

      <?php echo $this->fetch('content'); ?>
    </div>
    <div id="footer">
      <?php echo $this->Html->link(
          $this->Html->image('cake.power.gif', array('alt' => $title_for_layout, 'border' => '0')),
          'http://www.cakephp.org/',
          array('target' => '_blank', 'escape' => false)
        );
      ?>
    </div>
  </div>
</body>
</html>
