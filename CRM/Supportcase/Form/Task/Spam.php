<?php

/**
 * This class provides the functionality to resolve(set 'Spam' status) a group of case records.
 */
class CRM_Supportcase_Form_Task_Spam extends CRM_Supportcase_Form_SupportCaseTaskBase {

  public function getTitle() {
    return ts('Report spam cases');
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addDefaultButtons(ts('Report spam cases'), 'done');
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $updatedCaseCount = 0;
    $notUpdatedCaseCount = 0;

    foreach ($this->getCaseIds() as $caseId) {
      try {
        civicrm_api3('Case', 'create', ['id' => $caseId, 'status_id' => "spam"]);
        $updatedCaseCount++;
      } catch (CiviCRM_API3_Exception $e) {
        $notUpdatedCaseCount++;
      }
    }

    if ($updatedCaseCount > 0) {
      $message = ts('%count case is marked as spam.', ['plural' => '%count cases are marked as spam.', 'count' => $updatedCaseCount]);
      CRM_Core_Session::setStatus($message, ts('Report spam'), 'success');
    }

    if ($notUpdatedCaseCount > 0) {
      $message = ts('The case could not be marked as spam.', ['plural' => '%count cases could not be marked as spam.', 'count' => $notUpdatedCaseCount]);
      CRM_Core_Session::setStatus($message, ts('Some cases not report spam'), 'error');
    }
  }

}
