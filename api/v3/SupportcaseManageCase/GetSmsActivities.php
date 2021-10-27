<?php

/**
 * Get activities(type = sms) related to the case
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_sms_activities($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  try {
    $activities = civicrm_api3('Activity', 'get', [
      'is_deleted' => "0",
      'options' => ['limit' => 0],
      'case_id' => $params['case_id'],
      'activity_type_id' => ['IN' => ["SMS", "Inbound SMS"]],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    $activities = [];
  }

  $preparedActivities = [];
  if (!empty($activities['values'])) {
    foreach ($activities['values'] as $activity) {
      $preparedActivities[] = [
        'id' => $activity['id'],
        'subject' => !empty($activity['subject']) ? $activity['subject'] : '',
        'created_date' => $activity['created_date'],
        'details' => !empty($activity['details']) ? $activity['details'] : '',
      ];
    }
  }

  return civicrm_api3_create_success($preparedActivities);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_sms_activities_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
