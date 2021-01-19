<?php

/**
 * Finds contacts by phone email
 * Also it returns their subscriptions
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_find_contacts_by_email($params) {
  $emails = civicrm_api3('Email', 'get', [
    'sequential' => 1,
    'return' => ["contact_id", "contact_id.display_name"],
    'email' => $params['email'],
    'contact_id.is_deleted' => FALSE,
    'options' => ['limit' => 0],
  ]);

  $availableGroupIds = CRM_Supportcase_Utils_Setting::getSubscriptionsGroups();

  $preparedContacts = [];
  if (!empty($emails['values'])) {
    foreach ($emails['values'] as $email) {
      // dedupe by contact_id - only return once
      if (array_search($email['contact_id'], array_column($preparedContacts, 'id')) !== FALSE) {
        continue;
      }

      $groups = [];
      foreach ($availableGroupIds as $groupId) {
        if (CRM_Supportcase_Utils_Group::isContactInGroup($email['contact_id'], $groupId['id'], 'Added')) {
          $groups[] = $groupId['id'];
        }
      }

      $preparedContacts[] = [
        'id' => $email['contact_id'],
        'groups' => $groups,
        'display_name' => $email['contact_id.display_name'],
        'link' => CRM_Utils_System::url('civicrm/contact/view/', [
          'reset' => '1',
          'cid' => $email['contact_id'],
        ], FALSE, NULL, FALSE),
      ];
    }
  }

  return civicrm_api3_create_success([
    'contacts' => $preparedContacts,
    'available_groups' => CRM_Supportcase_Utils_Setting::getSubscriptionsGroups(),
  ]);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_quick_action_find_contacts_by_email_spec(&$params) {
  $params['email'] = [
    'name' => 'email',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Email name',
  ];
}
