<?php

/**
 * Gets case lock statuses
 */
function civicrm_api3_case_lock_get_locked_cases($params) {
  if (isset($params['case_ids']) && !is_array($params['case_ids'])) {
    return civicrm_api3_create_error('"case_ids" field must be array type.');
  }
  $caseIds = [];
  foreach ($params['case_ids'] as $caseId)  {
    if (!empty((int) $caseId)) {
      $caseIds[] = (int) $caseId;
    }
  }

  return civicrm_api3_create_success(CRM_Supportcase_BAO_CaseLock::getCasesLockStatus($caseIds));
}

function _civicrm_api3_case_lock_get_locked_cases_spec(&$params) {
  $params['case_ids'] = [
    'name'         => 'case_ids',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'List of case ids',
    'description'  => 'Api checks if there is exist locked cases.',
  ];
}
