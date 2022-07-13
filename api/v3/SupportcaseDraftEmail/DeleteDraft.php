<?php

function  civicrm_api3_supportcase_draft_email_delete_draft($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseDraftEmail_Delete($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_draft_email_delete_draft_spec(&$params) {
  $params['mailutils_message_id'] = [
    'name' => 'mailutils_message_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Mailutils message id',
  ];
}
