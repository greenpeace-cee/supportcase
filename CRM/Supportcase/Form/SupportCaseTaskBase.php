<?php

abstract class CRM_Supportcase_Form_SupportCaseTaskBase extends CRM_Core_Form_Task {

  /**
   * Cases data
   *
   * @var array
   */
  private $cases = [];

  /**
   * @return array
   */
  public function getCases() {
    return $this->cases;
  }

  /**
   * @return array
   */
  public function getCaseIds() {
    $ids = [];
    foreach ($this->getCases() as $case) {
      $ids[] = $case['id'];
    }

    return $ids;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return ts('Support Case Task');
  }

  /**
   * Check permission to access to task
   */
  protected function checkPermission() {}

  /**
   * Build all the data structures needed to build the form.
   *
   * @throws \CRM_Core_Exception
   */
  public function preProcess() {
    CRM_Utils_System::setTitle($this->getTitle());
    $this->checkPermission();
    $this->prepareCaseData();
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/ang/element.css');
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/case-tasks.css');
    $this->assign('cases', $this->getCases());
    $this->setRedirectUrl('civicrm/supportcase');
  }

  /**
   * Set redirection url
   * After tasks user will be redirected to this url
   *
   * @param $redirectUrl
   * @throws CRM_Core_Exception
   */
  private function setRedirectUrl($redirectUrl) {
    $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $this);
    $urlParams = 'force=1';
    if (CRM_Utils_Rule::qfKey($qfKey)) {
      $urlParams .= "&qfKey=$qfKey";
    }

    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext(CRM_Utils_System::url($redirectUrl, $urlParams));
  }

  private function prepareCaseData() {
    $caseIds = [];
    $queryParams = $this->get('queryParams');

    foreach ($queryParams as $values) {
      if (substr($values[0], 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
        $caseIds[] = substr($values[0], CRM_Core_Form::CB_PREFIX_LEN);
      }
    }

    $result = CRM_Core_DAO::executeQuery('SELECT id, subject FROM civicrm_case WHERE id IN(%1)', [
      1 => [implode(',', $caseIds), 'CommaSeparatedIntegers'],
    ]);

    $this->cases = $result->fetchAll();
  }

}
