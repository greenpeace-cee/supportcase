<?php

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

function _civicrm_api3_supportcase_email_getlist_params(&$request) {
  $request['params']['options']['limit'] = 0;
  $request['params']['search_string'] = (!empty($request['input'])) ? $request['input'] : '';
  $request['params']['email_id'] = (!empty($request['id'])) ? $request['id'] : '';
}

function _civicrm_api3_supportcase_email_getlist_output($result, $request, $entity, $fields) {
  $output = [];
  foreach ($result['values'] as $email) {
    $output[] = [
      'id' => $email['email_id'],
      'label' => $email['label'],
      'email' => $email['email'],
      'contact_id' => $email['contact_id'],
      'contact_display_name' => $email['contact_display_name'],
      'label_class' => $email['label_class'],
      'icon' => $email['icon'],
      'description' => $email['description'],
    ];
  }

  return $output;
}
