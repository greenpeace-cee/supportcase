<?php

/**
 * Get all comments related to case
 *
 * @param array $params
 *
 * @return array
 */
function  civicrm_api3_supportcase_comment_get($params) {
  return civicrm_api3_create_success((new CRM_Supportcase_Api3_SupportcaseComment_GetAll($params))->getResult(), $params);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_comment_get_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
