<?php

/**
 * Deletes comment
 */
function  civicrm_api3_supportcase_comment_delete($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseComment_Delete($params))->getResult(), $params);
}

function _civicrm_api3_supportcase_comment_delete_spec(&$params) {
  $params['id'] = [
    'name' => 'activity_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Activity id',
  ];
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
