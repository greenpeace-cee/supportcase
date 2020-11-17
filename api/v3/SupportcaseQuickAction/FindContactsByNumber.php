<?php

/**
 * Finds contacts by phone number
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_find_contacts_by_number($params) {
  $phones = civicrm_api3('Phone', 'get', [
    'sequential' => 1,
    'return' => ["contact_id", "contact_id.display_name", 'contact_id.do_not_sms'],
    'phone_numeric' => $params['phone_number'],
    'options' => ['limit' => 0],
  ]);

  $preparedContacts = [];
  if (!empty($phones['values'])) {
    foreach ($phones['values'] as $phone) {
      $preparedContacts[] = [
        'id' => $phone['contact_id'],
        'display_name' => $phone['contact_id.display_name'],
        'is_do_not_sms' => $phone['contact_id.do_not_sms'],
        'link' => CRM_Utils_System::url('civicrm/contact/view/', [
          'reset' => '1',
          'cid' => $phone['contact_id'],
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
function _civicrm_api3_supportcase_quick_action_find_contacts_by_number_spec(&$params) {
  $params['phone_number'] = [
    'name' => 'phone_number',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Phone number',
  ];
}
