<?php

/**
 * Finds contacts by phone number
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_apply_no_sms($params) {
  $preparedContacts = [];
  foreach ($params['contact_ids'] as $contactId) {
    $contact = civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'do_not_sms' => 1,
    ]);

    if (!empty($contact['values'][$contactId])) {
      $preparedContacts[] = [
        'id' => $contact['values'][$contactId]['id'],
        'display_name' => $contact['values'][$contactId]['display_name'],
        'link' => CRM_Utils_System::url('civicrm/contact/view/', [
          'reset' => '1',
          'cid' => $contact['values'][$contactId]['id'],
        ]),
      ];
    }
  }

  return civicrm_api3_create_success($preparedContacts);
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
