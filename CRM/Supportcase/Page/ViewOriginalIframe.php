<?php

use Civi\Api4\MailutilsMessage;

class CRM_Supportcase_Page_ViewOriginalIframe extends CRM_Core_Page {

  public function run() {
    $this->assign('id', CRM_Utils_Request::retrieve('id', 'Positive'));
    parent::run();
  }

}
