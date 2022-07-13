<?php

/**
 * Gets case info
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
  $managerIds = CRM_Supportcase_Utils_Case::findManagersIds($case);
  $clientIds = CRM_Supportcase_Utils_Case::findClientsIds($case);
  if (!empty($clientIds[0])) {
    $recentCaseForContactId = $clientIds[0];
  }

  $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
  $availableStatuses = CRM_Supportcase_Utils_Setting::getCaseStatusOptions();

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
    'related_contact_data' => CRM_Supportcase_Utils_CaseRelatedContact::get($case['id'], $clientIds),
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
  ];

  return civicrm_api3_create_success($caseInfo);
}

function _civicrm_api3_supportcase_manage_case_get_case_info_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
