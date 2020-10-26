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

  $recentCaseForContact = [];
  $clientIds = [];
  $clientsInfo = [];
  if (!empty($case['contacts'])) {
    foreach ($case['contacts'] as $contact) {
      if ($contact['role'] == 'Client') {
        $clientsInfo[$contact['contact_id']] = [
          'display_name' => $contact['sort_name'],
          'contact_id' => $contact['contact_id'],
          'email' => $contact['email'],
          'birth_date' => $contact['birth_date'],
          'tags' => CRM_Supportcase_Utils_Tags::getTags($contact['contact_id'], 'civicrm_contact'),
          'is_has_bpk' => false,//TODO in future
          'link' => CRM_Utils_System::url('civicrm/contact/view/', [
            'reset' => '1',
            'cid' => $contact['contact_id'],
          ]),
        ];
        if (empty($recentCaseForContact)) {
          $recentCaseForContact = $clientsInfo[$contact['contact_id']];
        }
        $clientIds[] = $contact['contact_id'];
      }
    }
  }

  $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);

  $caseInfo = [
    'id' => $case['id'],
    'subject' => $case['subject'],
    'clients' => $clientsInfo,
    'client_ids' => $clientIds,
    'start_date' => $case['start_date'],
    'status_id' => $case['status_id'],
    'available_statuses' => CRM_Supportcase_Utils_Case::getCaseStatuses(),
    "is_case_locked" => $isCaseLocked,
    "recent_case_for_contact" => $recentCaseForContact,
    "is_locked_by_self" => $isLockedBySelf,
    "locked_by_contact_id" => $lockedByContactId,
    "lock_message" => $lockMessage,
    "is_deleted" => $case['is_deleted'],
    "category_id" => $case[$categoryFieldName],
    "mange_case_update_lock_time" => CRM_Supportcase_Utils_Setting::getMangeCaseUpdateLockTime(),
    'available_categories' => CRM_Supportcase_Utils_Category::get(),
    'tags_ids' => CRM_Supportcase_Utils_Tags::getTagsIds($params['case_id'],'civicrm_case'),
    'available_tags' => CRM_Supportcase_Utils_Tags::getAvailableTags('civicrm_case'),
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
