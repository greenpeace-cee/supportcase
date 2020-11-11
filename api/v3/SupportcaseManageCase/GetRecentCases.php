<?php

/**
 * Gets recent cases for contact where case type = 'support_case'
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_recent_cases($params) {
  try {
    $contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $params['client_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Contact does not exist.', 'contact_does_not_exist');
  }

  $cases = civicrm_api3('Case', 'get', [
    'sequential' => 1,
    'return' => ["id", "subject", "status_id", "start_date"],
    'case_type_id' => "support_case",
    'client_id' => $params['client_id'],
    'is_deleted' => 0,
    'options' => ['limit' => $params['limit_per_page'], 'offset' => $params['limit_per_page'] * ($params['page_number'] - 1)],
  ]);

  $recentCases = [];

  if (!empty($cases['values'])) {
    foreach ($cases['values'] as $case) {
      $recentCases[] = array_merge($case, [
        'link' => CRM_Utils_System::url('civicrm/a/', NULL, TRUE, 'supportcase/manage-case/' . $case['id']),
      ]);
    }
  }

  return civicrm_api3_create_success($recentCases);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_recent_cases_spec(&$params) {
  $params['client_id'] = [
    'name' => 'client_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Client id',
    'description' => 'Case Client id(contact id)',
  ];
  $params['limit_per_page'] = [
    'name' => 'limit_per_page',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Limit per page',
  ];
  $params['page_number'] = [
    'name' => 'page_number',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Number of page',
  ];
}
