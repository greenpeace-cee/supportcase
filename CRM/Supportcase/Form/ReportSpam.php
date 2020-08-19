<?php

class CRM_Supportcase_Form_ReportSpam extends CRM_Core_Form {

  /**
   * Cases id
   *
   * @var int|null
   */
  private $caseId = NULL;

  /**
   * @return string
   */
  public function getTitle() {
    return ts('Report spam');
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addButtons(
      [
        [
          'type' => 'done',
          'name' => ts('Done'),
          'isDefault' => TRUE,
        ],
        [
          'type' => 'cancel',
          'name' => ts('Cancel'),
          'isDefault' => FALSE,
        ],
      ]
    );
  }

  /**
   * Processing needed for buildForm and later.
   */
  public function preProcess() {
    $caseId = CRM_Utils_Request::retrieve('id', 'Integer');
    if (empty($caseId)) {
      $this->assign('caseExistence', FALSE);
      return;
    }

    $case = new CRM_Case_BAO_Case();
    $case->id = $caseId;
    $caseExistence = $case->find(TRUE);
    if (!$caseExistence) {
      $this->assign('caseExistence', FALSE);
      return;
    }


    $this->assign('case', $case);
    $this->assign('caseExistence', TRUE);
    $this->caseId = $caseId;
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    if (empty($this->caseId)) {
      return;
    }

    try {
      civicrm_api3('Case', 'create', ['id' => $this->caseId, 'status_id' => "spam"]);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), ts('Some cases not report spam'), 'error');
      return;
    }

    CRM_Core_Session::setStatus(ts('Case is marked as spam.'), ts('Report spam'), 'success');
  }

}
