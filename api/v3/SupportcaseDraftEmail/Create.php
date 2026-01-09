<?php

function  civicrm_api3_supportcase_draft_email_create($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseDraftEmail_Create($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_draft_email_create_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['from_activity_id'] = [
    'name' => 'from_activity_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'From email activity id',
  ];
  $params['to_email_prefill_email_id'] = [
    'name' => 'to_email_prefill_email_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'To email prefill email id',
  ];
  $params['mode'] = [
    'name' => 'mode',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Mode',
    'options' => CRM_Supportcase_Utils_Email::getModeOptions(),
  ];
}
