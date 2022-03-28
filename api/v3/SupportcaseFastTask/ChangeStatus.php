<?php

/**
 * Change case status
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_fast_task_change_status($params) {
  $caseIds = explode(',', $params['case_ids']);
  if (!is_array($caseIds)) {
    throw new api_Exception('Cannot read case ids', 'cannot_read_case_ids');
  }
  foreach ($caseIds as $caseId) {
    civicrm_api3('Case', 'create', [
      'id' => $caseId,
      'status_id' => $params['status'],
      'track_status_change' => TRUE,
    ]);
  }

  return civicrm_api3_create_success(['message' => count($caseIds) . ' cases have been updated!']);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_fast_task_change_status_spec(&$params) {
  $params['case_ids'] = [
    'name' => 'case_ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Comma separated case ids',
  ];
  $params['status'] = [
    'name' => 'status',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Case Status name',
  ];
}
