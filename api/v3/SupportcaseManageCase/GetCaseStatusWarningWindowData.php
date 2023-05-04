<?php

function civicrm_api3_supportcase_manage_case_get_case_status_warning_window_data($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseManageCase_GetCaseStatusWarningWindowData($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_manage_case_get_case_status_warning_window_data_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['new_case_status_id'] = [
    'name' => 'new_case_status_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'New case status id',
  ];
  $params['context'] = [
    'name' => 'context',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Context',
  ];
}
