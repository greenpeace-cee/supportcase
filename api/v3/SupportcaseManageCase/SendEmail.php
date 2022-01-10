<?php

/**
 * Send case email
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_send_email($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseManageCase_SendEmail($params))->getResult(), $params);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_send_email_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['subject'] = [
    'name' => 'subject',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
  $params['body'] = [
    'name' => 'body',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_TEXT,
    'title' => 'Email body',
  ];
  $params['mode'] = [
    'name' => 'mode',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Mode',
    'options' => CRM_Supportcase_Utils_Email::getModeOptions(),
  ];
  $params['to_email_id'] = [
    'name' => 'to_email_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'To email id',
  ];
  $params['from_email_id'] = [
    'name' => 'from_email_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'From email id',
  ];
  $params['forward_file_ids'] = [
    'name' => 'forward_file_ids',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'This files will be copied to new activity',
  ];
  $params['cc_email_ids'] = [
    'name' => 'cc_email_ids',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'CC email ids',
  ];
  $params['email_activity_id'] = [
    'name' => 'email_activity_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Email activity id',
  ];
}
