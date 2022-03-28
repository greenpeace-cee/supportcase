<?php

/**
 * Get all comments related to case
 */
function  civicrm_api3_supportcase_comment_get($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseComment_GetAll($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_comment_get_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
