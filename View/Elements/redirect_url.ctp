<?php
if (empty($redirect_url)) {
  $redirect_url = $this->Form->value('redirect_url');
}
if (!empty($redirect_url)) {
  echo $this->Form->hidden('redirect_url', array(
    'name' => 'redirect_url',
    'value' => $redirect_url,
    'id' => null,
    'secure' => FormHelper::SECURE_SKIP
  ));
}
?>
