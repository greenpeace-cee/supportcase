<?php

/**
 * Send case email
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_send_email($params) {
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

  $body_text = CRM_Utils_String::htmlToText($params['body']);

  $activityID = CRM_Activity_BAO_Activity::createEmailActivity(
    $params['from_contact_id'],
    $params['subject'],
    NULL,
    $body_text,
    NULL,
    NULL,
    NULL,
    $case['id']
  );

  $sent = FALSE;
  if (CRM_Activity_BAO_Activity::sendMessage(
    $params['from_email'],
    $params['from_contact_id'],
    $params['to_contact_id'],
    $params['subject'],
    $body_text,
    $params['body'],
    $params['to_email'],
    $activityID
  )
  ) {
    return civicrm_api3_create_success(['message' => 'Case is updated!', 'case' => $case]);
  }

  throw new api_Exception('Error sending email.', 'email_error');
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_update_send_email_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['subject'] = [
    'name' => 'subject',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
}
