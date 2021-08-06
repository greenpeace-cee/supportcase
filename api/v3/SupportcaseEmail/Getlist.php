<?php

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_email_getlist_spec(&$params) {
  $params['input'] = [
      'name' => 'input',
      'api.required' => 1,
      'type' => CRM_Utils_Type::T_STRING,
      'title' => 'Search string',
  ];
}

/**
 * @param $request
 */
function _civicrm_api3_supportcase_email_getlist_params(&$request) {
  $request['params']['options']['limit'] = 0;
  $request['params']['search_string'] = (!empty($request['input'])) ? $request['input'] : '';
  $request['params']['search_pseudo_id'] = (!empty($request['id'])) ? $request['id'] : '';
}

/**
 * @param array $result
 * @param array $request
 *
 * @param $entity
 * @param $fields
 * @return array
 * @see _civicrm_api3_generic_getlist_output
 *
 */
function _civicrm_api3_supportcase_email_getlist_output($result, $request, $entity, $fields) {
  $output = [];
  foreach ($result['values'] as $item) {
    $output[] = [
        'id' => $item['id'],
        'label' => $item['label'],
        'description' => []
    ];
  }

  return $output;
}
