<?php

/**
 * The form uses to view angularJs page in popup
 * The form like a wrapper which runs angularJs page in iframe
 */
class CRM_Supportcase_Page_ManageCaseAngularWrap extends CRM_Core_Page {

  /**
   * The main function that is called when the page loads
   */
  public function run() {
    $caseId = CRM_Utils_Request::retrieve('case_id', 'String');
    $angularUrl = CRM_Utils_System::url('civicrm/a/', NULL, TRUE, 'supportcase/manage-case/' . $caseId . '/in-iframe');
    $this->assign('angularUrl', $angularUrl );

    return parent::run();
  }

}
