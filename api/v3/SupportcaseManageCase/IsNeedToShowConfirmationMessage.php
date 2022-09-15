<?php

/**
 * Use to know is need to show confirmation message while saving case client
 */
function civicrm_api3_supportcase_manage_case_is_need_to_show_confirmation_message($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseManageCase_IsNeedToShowConfirmationMessage($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_manage_case_is_need_to_show_confirmation_message_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['new_case_client_id'] = [
    'name' => 'new_case_client_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'New case client id',
  ];
}
