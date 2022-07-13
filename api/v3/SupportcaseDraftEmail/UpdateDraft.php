<?php

function  civicrm_api3_supportcase_draft_email_update_draft($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseDraftEmail_Update($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_draft_email_update_draft_spec(&$params) {
  $params['mailutils_message_id'] = [
    'name' => 'mailutils_message_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Mailutils message id',
  ];
  $params['subject'] = [
    'name' => 'subject',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
  $params['body'] = [
    'name' => 'body',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_TEXT,
    'title' => 'Email body',
  ];
  $params['to_email_id'] = [
    'name' => 'to_email_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'To email id',
  ];
  $params['from_email_id'] = [
    'name' => 'from_email_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'From email id',
  ];
  $params['cc_email_ids'] = [
    'name' => 'cc_email_ids',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'CC email ids',
  ];
}
