<?php

/**
 * Updates comment
 */
function  civicrm_api3_supportcase_comment_update($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseComment_Update($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_comment_update_spec(&$params) {
  $params['activity_id'] = [
    'name' => 'activity_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Activity Id',
  ];
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
