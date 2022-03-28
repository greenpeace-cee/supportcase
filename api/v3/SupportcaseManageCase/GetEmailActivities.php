<?php

/**
 * Get activities(type = email) related to the case
 */
function civicrm_api3_supportcase_manage_case_get_email_activities($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseManageCase_GetEmailActivities($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_manage_case_get_email_activities_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
