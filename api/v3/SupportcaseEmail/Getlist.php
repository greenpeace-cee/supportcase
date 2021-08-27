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

  $params['email_id'] = [
    'name' => 'email_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Search string',
  ];
}

/**
 * @param $request
 */
function _civicrm_api3_supportcase_email_getlist_params(&$request) {
  $request['params']['options']['limit'] = 0;
  $request['params']['search_string'] = (!empty($request['input'])) ? $request['input'] : '';
  $request['params']['email_id'] = (!empty($request['id'])) ? $request['id'] : '';
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
  foreach ($result['values'] as $email) {
    $output[] = [
        'id' => $email['email_id'],
        'label' => $email['label'],
        'contact_id' => $email['contact_id'],
        'contact_display_name' => $email['contact_display_name'],
        'description' => []
    ];
  }

  // TODO: fix it in another way
  // this is hack stops javaScript endless loop in entity ref inputs
  if (empty($output)) {
    throw new api_Exception('Empty output', 'empty');
  }

  return $output;
}
