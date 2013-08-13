<?php
App::uses('CrudAppHelper', 'Crud.View/Helper');

class CrudHelper extends CrudAppHelper {

/**
 * List of helpers used by this helper
 *
 * @var array
 */
  public $helpers = array(
    'Form'
  );


/**
 * Returns a hidden input for the redirect_url if it exists
 * in the request querystring, view variables, form data
 *
 * @var array
 */
  public function redirectUrl() {
    $redirect_url = $this->_View->request->query('redirect_url');
    if (!empty($this->_View->viewVars['redirect_url'])) {
      $redirect_url = $this->_View->viewVars['redirect_url'];
    } else {
      $redirect_url = $this->Form->value('redirect_url');
    }

    if (!empty($redirect_url)) {
      return $this->Form->hidden('redirect_url', array(
        'name' => 'redirect_url',
        'value' => $redirect_url,
        'id' => null,
        'secure' => FormHelper::SECURE_SKIP
      ));
    }

    return null;
  }

}
