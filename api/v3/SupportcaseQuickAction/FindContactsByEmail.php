<?php

/**
 * Finds contacts by phone email
 * Also it returns their subscriptions
 */
function civicrm_api3_supportcase_quick_action_find_contacts_by_email($params) {
  $emails = civicrm_api3('Email', 'get', [
    'sequential' => 1,
    'return' => ["contact_id", "contact_id.is_opt_out"],
    'email' => $params['email'],
    'contact_id.is_deleted' => FALSE,
    'options' => ['limit' => 0],
  ]);

  $availableGroups = CRM_Supportcase_Utils_Setting::getSubscriptionsGroups();
  $foundContactIds = [];
  $tableData = [];

  $headers = [];
  $headers[] = [
    'label' => 'Contact',
    'description' => 'Contact info',
    'table_data_key' => 'contact_id',
    'is_dynamic_header' => false,
  ];
  foreach ($availableGroups as $group) {
    $headers[] = [
      'label' => $group['title'],
      'description' => "Newsletter group '{$group['title']}'",
      'table_data_key' => $group['name'],
      'is_dynamic_header' => true,
    ];
  }
  $headers[] = [
    'label' => 'NO BULK EMAILS (User Opt Out)',
    'description' => 'Opt-out from all bulk emails',
    'table_data_key' => 'contact_is_opt_out',
    'is_dynamic_header' => false,
  ];

  if (!empty($emails['values'])) {
    foreach ($emails['values'] as $email) {
      // dedupe by contact_id - only return once
      if (in_array($email['contact_id'], $foundContactIds)) {
        continue;
      }
      $foundContactIds[] = $email['contact_id'];

      $contactData = [
        'contact_id' => $email['contact_id'],
        'contact_is_opt_out' => (bool) $email['contact_id.is_opt_out'],
      ];

      foreach ($availableGroups as $group) {
        $contactData[$group['name']] = CRM_Supportcase_Utils_Group::isContactInGroup($email['contact_id'], $group['id'], 'Added');
      }

      $tableData[] = $contactData;
    }
  }

  return civicrm_api3_create_success([
    'table_headers' => $headers,
    'table_data' => $tableData,
    'available_groups' => CRM_Supportcase_Utils_Setting::getSubscriptionsGroups(),
  ]);
}

function _civicrm_api3_supportcase_quick_action_find_contacts_by_email_spec(&$params) {
  $params['email'] = [
    'name' => 'email',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Email name',
  ];
}
