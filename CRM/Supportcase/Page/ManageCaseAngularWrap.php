<?php

/**
 * The form uses to view angularJs page in popup
 * The form like a wrapper which runs angularJs page in iframe
 */
class CRM_Supportcase_Page_ManageCaseAngularWrap extends CRM_Core_Page {

  /**
   * Iframe height with 'Manage Case' angular page
   *
   * @var int
   */
  private $iframeHeight = 1200;

  /**
   * Iframe height with 'Manage Case' angular page
   * which is opened in modal window
   *
   * @var int
   */
  private $iframeHeightInModal = 650;

  /**
   * The main function that is called when the page loads
   */
  public function run() {
    $caseId = CRM_Utils_Request::retrieve('case_id', 'String');
    $isRunInModalWindow = ($this->_print == 'json');
    $iframeHeight = $isRunInModalWindow ? CRM_Supportcase_Utils_Setting::getAngularIframeHeightInModal() : CRM_Supportcase_Utils_Setting::getAngularIframeHeight();
    $notScrollBlockHeight = 90;
    $scrollBlockHeight = $iframeHeight - $notScrollBlockHeight;
    $angularUrl = CRM_Utils_System::url('civicrm/a/', NULL, TRUE, 'supportcase/manage-case/' . $caseId );
    $angularUrl .= $isRunInModalWindow ? '/' . $scrollBlockHeight : '';

    $this->assign('iframeHeight', $iframeHeight);
    $this->assign('scrollBlockHeight', $scrollBlockHeight);
    $this->assign('notScrollBlockHeight', $notScrollBlockHeight);
    $this->assign('angularUrl', $angularUrl);
    $this->assign('isRunInModalWindow', $isRunInModalWindow);

    return parent::run();
  }

}
