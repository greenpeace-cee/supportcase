<?php

/**
 * Changes category
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_fast_task_change_category($params) {
  $categoryOptions = CRM_Supportcase_Utils_Category::getOptions();
  $availableCategories = array_keys($categoryOptions);
  if (!in_array($params['category_value'], $availableCategories)) {
    throw new api_Exception('Category does not exist.', 'category_does_not_exist');
  }

  $categoryCustomFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
  $caseIds = explode(',', $params['case_ids']);
  if (!is_array($caseIds)) {
    throw new api_Exception('Cannot read case ids', 'cannot_read_case_ids');
  }
  $errorCaseCounter = 0;
  $sussesCaseCounter = 0;

  foreach ($caseIds as $caseId) {
    try {
      civicrm_api3('Case', 'create', ['id' => $caseId, $categoryCustomFieldName => $params['category_value']]);
      $sussesCaseCounter++;
    } catch (CiviCRM_API3_Exception $e) {
      $errorCaseCounter++;
    }
  }

  $message = '';
  $message .= ($errorCaseCounter > 0) ? $errorCaseCounter . ' - cases updating category returns error.' : '';
  $message .= ($sussesCaseCounter > 0) ? $sussesCaseCounter . ' - successfully updated cases.' : '';

  return civicrm_api3_create_success(['message' => $message]);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_fast_task_change_category_spec(&$params) {
  $params['case_ids'] = [
    'name' => 'case_ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Comma separated case ids',
  ];
  $params['category_value'] = [
    'name' => 'category_value',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'category value',
  ];
}
