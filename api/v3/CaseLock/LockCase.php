<?php

/**
 * Lock the case
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_case_lock_lock_case($params) {
  $case = new CRM_Case_BAO_Case();
  $case->id = $params['case_id'];
  $caseExistence = $case->find(TRUE);
  if (empty($caseExistence)) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  $contactId = CRM_Core_Session::getLoggedInContactID();
  if (empty($contactId)) {
    throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
  }

  if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($params['case_id'], $contactId)) {
    throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
  }

  $lockCase = CRM_Supportcase_BAO_CaseLock::lockCase($params['case_id'], $contactId);

  return civicrm_api3_create_success($lockCase);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_case_lock_lock_case_spec(&$params) {
  $params['case_id'] = [
    'name'         => 'case_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Case id',
    'description'  => 'The Case which will be locked.',
  ];
}
