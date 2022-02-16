<?php

/**
 * This class provides the functionality to delete a group of case records.
 */
class CRM_Supportcase_Form_Task_Delete extends CRM_Supportcase_Form_SupportCaseTaskBase {

  public function getTitle() {
    return ts('Delete cases');
  }

  /**
   * Check permission to access to task
   */
  protected function checkPermission() {
    if (!CRM_Core_Permission::checkActionPermission('CiviCase', CRM_Core_Action::DELETE)) {
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this page.'));
    }
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addDefaultButtons(ts('Delete cases'), 'done');
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $isMoveToTrash = TRUE;
    $deleted = 0;
    $failed = 0;

    foreach ($this->getCaseIds() as $caseId) {
      if (CRM_Case_BAO_Case::deleteCase($caseId, $isMoveToTrash)) {
        $deleted++;
      } else {
        $failed++;
      }
    }

    if ($deleted > 0) {
      if ($isMoveToTrash) {
        $message = ts('%count case moved to trash.', ['plural' => '%count cases moved to trash.', 'count' => $deleted]);
      } else {
        $message = ts('%count case permanently deleted.', ['plural' => '%count cases permanently deleted.', 'count' => $deleted]);
      }
      CRM_Core_Session::setStatus($message, ts('Removed'), 'success');
    }

    if ($failed > 0) {
      CRM_Core_Session::setStatus(ts('1 could not be deleted.', ['plural' => '%count could not be deleted.', 'count' => $failed]), ts('Error'), 'error');
    }
  }

}
