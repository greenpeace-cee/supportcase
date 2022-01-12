<?php

/**
 * Gets case info
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_case_info($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  $caseLock = civicrm_api3('CaseLock', 'get_locked_cases', [
    'case_ids' => [$params['case_id']],
  ]);

  $isCaseLocked = false;
  $isLockedBySelf = false;
  $lockedByContactId = '';
  $lockMessage = '';

  if (!empty($caseLock['values'][0])) {
    $isCaseLocked = $caseLock['values'][0]['is_case_locked'];
    $isLockedBySelf = $caseLock['values'][0]['is_locked_by_self'];
    $lockedByContactId = $caseLock['values'][0]['contact_id'];
    $lockMessage = $caseLock['values'][0]['lock_message'];
    if ($isCaseLocked && !$isLockedBySelf) {
      throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
    }
  }

  $recentCaseForContactId = '';
  $clientIds = [];
  $managerIds = [];
  if (!empty($case['contacts'])) {
    foreach ($case['contacts'] as $contact) {
      if ($contact['role'] == 'Client') {
        $clientIds[] = $contact['contact_id'];
      }
      if ($contact['role'] == 'Case Coordinator is') {
        $managerIds[] = $contact['contact_id'];
      }
    }

    if (!empty($clientIds[0])) {
      $recentCaseForContactId = $clientIds[0];
    }
  }

  $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
  $availableStatuses = CRM_Supportcase_Utils_Case::getCaseStatuses();

  $caseStatusSettings = [];
  foreach ($availableStatuses as $status) {
    if ($status['name'] == 'spam') {
      $caseStatusSettings['spam'] = $status['value'];
    } elseif ($status['name'] == 'Open') {
      $caseStatusSettings['ongoing'] = $status['value'];
    } elseif ($status['name'] == 'Closed') {
      $caseStatusSettings['resolve'] = $status['value'];
    } elseif ($status['name'] == 'Urgent') {
      $caseStatusSettings['urgent'] = $status['value'];
    }
  }

  $caseInfo = [
    'id' => $case['id'],
    'subject' => $case['subject'],
    'client_ids' => $clientIds,
    'managers_ids' => $managerIds,
    'start_date' => $case['start_date'],
    'status_id' => $case['status_id'],
    'available_statuses' => $availableStatuses,
    "is_case_locked" => $isCaseLocked,
    "recent_case_for_contact_id" => $recentCaseForContactId,
    "is_locked_by_self" => $isLockedBySelf,
    "locked_by_contact_id" => $lockedByContactId,
    "lock_message" => $lockMessage,
    "is_deleted" => $case['is_deleted'],
    "category_id" => $case[$categoryFieldName],
    'available_categories' => CRM_Supportcase_Utils_Category::get(),
    'tags_ids' => CRM_Supportcase_Utils_Tags::getTagsIds($params['case_id'],'civicrm_case'),
    'available_tags' => CRM_Supportcase_Utils_Tags::getAvailableTags('civicrm_case'),
    'phone_number_for_do_not_sms_action' => CRM_Supportcase_Utils_Case::findPhoneNumberInActivities($params['case_id']),
    'email_for_manage_email_subscriptions' => CRM_Supportcase_Utils_Case::findEmailInActivities($params['case_id']),
    'settings' => [
      'case_status_ids' => $caseStatusSettings,
      'mange_case_update_lock_time' => CRM_Supportcase_Utils_Setting::getMangeCaseUpdateLockTime(),
    ],
    'new_email_prefill_fields' => [
      'subject' => $case['subject'],
      'from_email_id' => !empty($clientIds[0]) ? CRM_Supportcase_Utils_EmailSearch::getEmailIdByContactId($clientIds[0]) : '',
      'to_email_id' => '',
      'cc_email_ids' => '',
      'email_body' => '',
    ]
  ];

  return civicrm_api3_create_success($caseInfo);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_case_info_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
