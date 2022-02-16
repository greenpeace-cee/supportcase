<?php

use Civi\Api4\MailutilsMessage;

class CRM_Supportcase_Page_ViewOriginal extends CRM_Core_Page {

  public function run() {
    $email = civicrm_api3('SupportcaseEmail', 'getoriginal', [
      'activity_id' => CRM_Utils_Request::retrieve('id', 'Positive'),
    ]);
    CRM_Utils_System::setHttpHeader('Content-Security-Policy', 'sandbox allow-popups allow-popups-to-escape-sandbox');
    echo $email['values'];
    CRM_Utils_System::civiExit();
  }

}
