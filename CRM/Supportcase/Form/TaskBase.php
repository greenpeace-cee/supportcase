<?php

/**
 * This class generates form task actions for CiviCase.
 */
abstract class CRM_Supportcase_Form_TaskBase extends CRM_Core_Form_Task {

  public function getTitle() {
    return ts('Support Dashboard');
  }

  /**
   * Are we operating in "single mode", i.e. deleting one specific case?
   *
   * @var bool
   */
  protected $_single = FALSE;

  /**
   * Build all the data structures needed to build the form.
   *
   * @throws \CRM_Core_Exception
   */
  public function preProcess() {
    CRM_Utils_System::setTitle($this->getTitle());
    $this->checkPermission();
    parent::preProcess();
    CRM_Core_Resources::singleton()->addStyleFile('supportcase', 'css/case-tasks.css');
    $this->assign('cases', $this->getCasesSubjects());
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
   * Must be set to entity table name (eg. civicrm_participant) by child class
   * @var string
   */
  public static $tableName = 'civicrm_case';

  /**
   * Must be set to entity shortname (eg. event)
   * @var string
   */
  public static $entityShortname = 'case';

  /**
   * @inheritDoc
   */
  public function setContactIDs() {
    // @todo Parameters shouldn't be needed and should be class member
    // variables instead, set appropriately by each subclass.
    $this->_contactIds = $this->getContactIDsFromComponent($this->_entityIds,
      'civicrm_case_contact', 'case_id'
    );
  }

  /**
   * Get the query mode (eg. CRM_Core_BAO_Query::MODE_CASE)
   *
   * @return int
   */
  public function getQueryMode() {
    return CRM_Contact_BAO_Query::MODE_CASE;
  }

  /**
   * Override of CRM_Core_Form_Task::orderBy()
   *
   * @return string
   */
  public function orderBy() {
    if (empty($this->_entityIds)) {
      return '';
    }
    $order_array = [];
    foreach ($this->_entityIds as $item) {
      // Ordering by conditional in mysql. This evaluates to 0 or 1, so we
      // need to order DESC to get the '1'.
      $order_array[] = 'case_id = ' . CRM_Core_DAO::escapeString($item) . ' DESC';
    }
    return 'ORDER BY ' . implode(',', $order_array);
  }

  /**
   * Check permission to access to task
   */
  protected function checkPermission() {}

  /**
   * Get cases subjects by ids
   */
  private function getCasesSubjects() {
    try {
      $cases = civicrm_api3('Case', 'get', [
        'sequential' => 1,
        'id' => ['IN' => $this->_entityIds],
        'options' => ['limit' => 0],
        "return" => ["subject"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return (!empty($cases['values'])) ? $cases['values'] : [];
  }

}
