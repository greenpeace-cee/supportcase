<?php

/**
 * This class provides the functionality to change category in group of case records.
 */
class CRM_Supportcase_Form_Task_ChangeCategory extends CRM_Supportcase_Form_TaskBase {

  public function getTitle() {
    return ts('Change category');
  }

  /**
   * Check permission to access to task
   */
  protected function checkPermission() {
    if (!CRM_Core_Permission::checkActionPermission('CiviCase', CRM_Core_Action::UPDATE)) {
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this page.'));
    }
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->add('select', 'case_category', ts('New Case Category'), CRM_Supportcase_Utils_Category::getOptions(), true);
    $this->addDefaultButtons(ts('Change cases category'), 'done');
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $categoryCustomFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', 'support_case_details', TRUE);
    $submitValues = $this->exportValues();
    $categories = CRM_Supportcase_Utils_Category::getOptions();
    $updatedCaseCount = 0;
    $notUpdatedCaseCount = 0;

    foreach ($this->_entityIds as $caseId) {
      try {
        civicrm_api3('Case', 'create', ['id' => $caseId, $categoryCustomFieldName => $submitValues['case_category']]);
        $updatedCaseCount++;
      } catch (CiviCRM_API3_Exception $e) {
        $notUpdatedCaseCount++;
      }
    }

    if ($updatedCaseCount > 0) {
      $message = ts(
        '%count case has changed category to "%category"',
        [
          'plural' => '%count cases have changed category to "%category".',
          'count' => $updatedCaseCount,
          'category' => $categories[$submitValues['case_category']]
        ]
      );
      CRM_Core_Session::setStatus($message, ts('Case Change Category'), 'success');
    }

    if ($notUpdatedCaseCount > 0) {
      $message = ts(
        '%count case has not changed category to "%category"',
        [
          'plural' => '%count cases have not changed category to "%category".',
          'count' => $updatedCaseCount,
          'category' => $categories[$submitValues['case_category']]
        ]
      );
      CRM_Core_Session::setStatus($message, ts('Some cases has not changed category'), 'error');
    }
  }

}
