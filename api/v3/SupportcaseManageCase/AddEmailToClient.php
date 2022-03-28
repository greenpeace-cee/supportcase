<?php

function civicrm_api3_supportcase_manage_case_add_email_to_client($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseManageCase_AddEmailToClient($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_manage_case_add_email_to_client_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['email'] = [
    'name' => 'email',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'email',
  ];
}
