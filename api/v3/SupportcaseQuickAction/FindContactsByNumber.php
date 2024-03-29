<?php

/**
 * Finds contacts by phone number
 */
function civicrm_api3_supportcase_quick_action_find_contacts_by_number($params) {
  $phones = civicrm_api3('Phone', 'get', [
    'sequential' => 1,
    'return' => ["contact_id", 'contact_id.do_not_sms'],
    'phone_numeric' => preg_replace('/[^\d]/', '', $params['phone_number']),
    'contact_id.is_deleted' => FALSE,
    'options' => ['limit' => 0],
  ]);

  $preparedContacts = [];
  if (!empty($phones['values'])) {
    foreach ($phones['values'] as $phone) {
      // dedupe by contact_id - only return once
      if (array_search($phone['contact_id'], array_column($preparedContacts, 'id')) !== FALSE) {
        continue;
      }
      $preparedContacts[] = [
        'id' => $phone['contact_id'],
        'is_do_not_sms' => $phone['contact_id.do_not_sms'],
      ];
    }
  }

  return civicrm_api3_create_success($preparedContacts);
}

function _civicrm_api3_supportcase_quick_action_find_contacts_by_number_spec(&$params) {
  $params['phone_number'] = [
    'name' => 'phone_number',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Phone number',
  ];
}
