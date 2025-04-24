<?php

use Civi\Api4\Relationship;

/**
 * Update case client
 */
function civicrm_api3_supportcase_manage_case_change_client($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  $contactId = CRM_Core_Session::getLoggedInContactID();
  if (empty($contactId)) {
    throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
  }

  if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($params['case_id'], $contactId)) {
    throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
  }

  if (!CRM_Supportcase_Utils_Setting::isCaseToolsExtensionEnable()) {
    throw new api_Exception('Cannot update case client. Please install "at.greenpeace.casetools" extension.', 'cannot_update_case_client');
  }

  try {
    civicrm_api3('Contact', 'getsingle', [
      'id' => $params['new_case_client_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Client id does not exist', 'client_id_does_not_exist');
  }

  $currentClientId = '';
  foreach ($case['client_id'] as $clientId) {
    $currentClientId = $clientId;
  }

  if ($currentClientId == $params['new_case_client_id']) {
    throw new api_Exception('New client id and current client id is the same.', 'the_same_current_client_id_and_new_client_id');
  }

  $result = civicrm_api3('Case', 'change_clients', [
    'id' => $params['case_id'],
    'client_id' => $params['new_case_client_id'],
  ]);
  $activities = $result['values']['rows'][0]['activity_id'] ?? [];
  if (count($activities) > 0) {
    // extract all emails used by the contact in any moved activities
    $contactEmailsInCase = CRM_Supportcase_Utils_MailutilsMessageParty::getMessagePartyEmailByActivitiesAndContact(
      $activities,
      $currentClientId
    );
    // add emails to new contact if necessary
    CRM_Supportcase_Utils_Email::addSupportEmailsToContact(
      $params['new_case_client_id'],
      $contactEmailsInCase
    );
    // update contact_id in MailutilsMessageParty to new client
    CRM_Supportcase_Utils_MailutilsMessageParty::updateMessagePartyContactByActivitiesAndContact(
      $activities,
      $currentClientId,
      $params['new_case_client_id']
    );
  }

  // create a dupe relationship between old and new client
  CRM_Supportcase_Utils_DuplicateContacts::createDuplicateRelationship(
    $params['case_id'],
    $params['new_case_client_id'],
    $currentClientId
  );

  return civicrm_api3_create_success();
}

function _civicrm_api3_supportcase_manage_case_change_client_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['new_case_client_id'] = [
    'name' => 'new_case_client_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'New case client id',
  ];
}
