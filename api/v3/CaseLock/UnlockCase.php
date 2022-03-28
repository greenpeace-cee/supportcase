<?php

/**
 * Unlock case
 */
function civicrm_api3_case_lock_unlock_case($params) {
  $case = new CRM_Case_BAO_Case();
  $case->id = $params['case_id'];
  $caseExistence = $case->find(TRUE);
  if (empty($caseExistence)) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  CRM_Supportcase_BAO_CaseLock::unlockCase($params['case_id']);

  return civicrm_api3_create_success(['message' => 'Case id = ' . $params['case_id'] . ' is unlocked.']);
}

function _civicrm_api3_case_lock_unlock_case_spec(&$params) {
  $params['case_id'] = [
    'name'         => 'case_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Case id',
    'description'  => 'The Case which will be unlocked.',
  ];
}
