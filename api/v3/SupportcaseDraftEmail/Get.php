<?php

function  civicrm_api3_supportcase_draft_email_get($params) {
  if (!empty($params['mailutils_message_id'])) {
    $getParams = ['mailutils_message_id' => $params['mailutils_message_id'], 'return' => $params['return']];
    return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_ByMailUtilsMessageId($getParams))->getResult(), $getParams);
  }

  if (!empty($params['case_id'])) {
    $getParams = ['case_id' => $params['case_id'], 'return' => $params['return']];
    return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_ByCaseId($getParams))->getResult(), $getParams);
  }

  if (empty($params['case_id']) && empty($params['mailutils_message_id'])) {
    throw new api_Exception('One of the "mailutils_message_id" and "case_id" is required.', 'required_fields');
  }

  return civicrm_api3_create_success([], $params);
}

function _civicrm_api3_supportcase_draft_email_get_spec(&$params) {
  $params['mailutils_message_id'] = [
    'name' => 'mailutils_message_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Mailutils message id',
  ];
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case Id',
  ];
}
