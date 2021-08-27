<?php

/**
 * Send case email
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_send_email($params) {
  //TODO: handle reply_mode field, or remove it
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

  $toEmails = CRM_Supportcase_Utils_EmailSearch::searchByIds($params['to_email_id']);
  if (empty($toEmails)) {
    throw new api_Exception('Cannot find to email', 'cannot_find_to_email');
  }

  $fromEmails = CRM_Supportcase_Utils_EmailSearch::searchByIds($params['from_email_id']);
  if (empty($fromEmails)) {
    throw new api_Exception('Cannot find from email', 'cannot_find_from_email');
  }

  $ccEmails = CRM_Supportcase_Utils_EmailSearch::searchByIds($params['cc_email_ids']);
  $fromEmail = $fromEmails[0];
  $toEmail = $toEmails[0];
  $bodyText = CRM_Utils_String::htmlToText($params['body']);
  $activityID = CRM_Activity_BAO_Activity::createEmailActivity(
    $fromEmail['contact_id'],
    $params['subject'],
    NULL,
    $bodyText,
    NULL,
    NULL,
    NULL,
    $case['id']
  );

  $ccEmailsList = [];
  foreach ($ccEmails as $email) {
    $ccEmailsList[] = $email['email'];
  }

  $isSent = CRM_Activity_BAO_Activity::sendMessage(
    $fromEmail['email'],
    $fromEmail['contact_id'],
    $toEmail['contact_id'],
    $params['subject'],
    $bodyText,
    $params['body'],
    $toEmail['email'],
    $activityID,
    NULL,
    implode(', ', $ccEmailsList),
  );

  if ($isSent) {
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
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
  $params['body'] = [
    'name' => 'body',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_TEXT,
    'title' => 'Email body',
  ];
  $params['reply_mode'] = [
    'name' => 'reply_mode',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Reply mode',
  ];
  $params['to_email_id'] = [
    'name' => 'to_email_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'To email id',
  ];
  $params['from_email_id'] = [
    'name' => 'from_email_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'From email id',
  ];
  $params['cc_email_ids'] = [
    'name' => 'cc_email_ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'From email id',
  ];
}
