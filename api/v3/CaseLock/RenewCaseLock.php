<?php

/**
 * Renews case locking
 *
 * @param $params
 *
 * @return array
 * @throws Exception
 */
function civicrm_api3_case_lock_renew_case_lock($params) {
  $caseLockExistence = CRM_Supportcase_BAO_CaseLock::isCaseLockExist($params['case_lock_id']);
  if (!$caseLockExistence) {
    throw new api_Exception('Case lock does not exist.', 'case_lock_does_not_exist');
  }

  $lockCase = CRM_Supportcase_BAO_CaseLock::renewLockCase($params['case_lock_id']);

  return civicrm_api3_create_success($lockCase);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_case_lock_renew_case_lock_spec(&$params) {
  $params['case_lock_id'] = [
    'name'         => 'case_lock_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Case lock  id',
    'description'  => 'The case lock id which will be renewed',
  ];
}
