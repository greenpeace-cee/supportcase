<?php

abstract class CRM_Supportcase_Form_SupportCaseTaskBase extends CRM_Core_Form_Task {

  /**
   * Cases from database with applied search params on dashboard
   *
   * @var array
   */
  private $cases = [];

  /**
   * Selected cases by UI
   *
   * @var array
   */
  private $selectedCases = [];

  /**
   * @return array
   */
  public function getCases() {
    return $this->cases;
  }

  /**
   * @return array
   */
  public function getSelectedCases() {
    return $this->selectedCases;
  }

  /**
   * @return array
   */
  public function getSelectedCaseIds() {
    $ids = [];
    foreach ($this->getSelectedCases() as $case) {
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
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/case-tasks.css');
    $this->assign('cases', $this->getSelectedCases());
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

  /**
   * Prepare data for task
   * - selected cases by UI
   * - cases from database with applied search params on dashboard
   *
   * @throws CRM_Core_Exception
   */
  private function prepareCaseData() {
    $selectedCases = [];
    $selectedCaseIds = [];
    $cases = [];
    $queryParams = $this->get('queryParams');

    foreach ($queryParams as $values) {
      if (substr($values[0], 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
        $selectedCaseIds[] = substr($values[0], CRM_Core_Form::CB_PREFIX_LEN);
      }
    }

    $query = new CRM_Contact_BAO_Query($queryParams, NULL, NULL, FALSE, FALSE);
    $query->_distinctComponentClause = " ( civicrm_case.id )";
    $query->_groupByComponentClause = " GROUP BY civicrm_case.id ";;
    $query->_select[] = 'civicrm_case.subject as case_subject';
    $query->_select[] = 'civicrm_case.id as case_id';
    $result = $query->searchQuery(0, 0);

    while ($result->fetch()) {
      $caseId = $result->case_id;
      $case = [
        'id' => $caseId,
        'subject' => $result->case_subject,
      ];

      $cases[$caseId] = $case;
      if (in_array($caseId, $selectedCaseIds)) {
        $selectedCases[] =  $case;
      }
    }

    $this->selectedCases = $selectedCases;
    $this->cases = $cases;
  }

}
