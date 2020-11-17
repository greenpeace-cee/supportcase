<?php

/**
 * Finds contacts by phone number
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_apply_no_sms($params) {
  $successContacts = [];
  foreach ($params['contact_ids'] as $contactId) {
    civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'do_not_sms' => 1,
    ]);
  }

  return civicrm_api3_create_success(['message' => '"do not sms" is checked to selected contacts.']);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_quick_action_apply_no_sms_spec(&$params) {
  $params['contact_ids'] = [
    'name' => 'contact_ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Contact ids',
  ];
}
