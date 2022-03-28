<?php

/**
 * Creates comment
 */
function  civicrm_api3_supportcase_comment_create($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseComment_Create($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_comment_create_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['comment'] = [
    'name' => 'comment',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_TEXT,
    'title' => 'Comment',
  ];
}
