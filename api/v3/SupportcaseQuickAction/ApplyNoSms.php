<?php

/**
 * Finds contacts by phone number
 */
function civicrm_api3_supportcase_quick_action_apply_no_sms($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  foreach ($params['contact_ids'] as $contactId) {
    civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'do_not_sms' => 1,
    ]);
  }

  if (CRM_Supportcase_Utils_Setting::isCaseToolsExtensionEnable()) {
    $doNotSmsTagId = CRM_Supportcase_Utils_Tags::getTagId(CRM_Supportcase_Install_Entity_Tag::DO_NOT_SMS);
    if (!empty($doNotSmsTagId)) {
      civicrm_api3('Case', 'create', [
        'id' => $params['case_id'],
        'tags_ids' => [$doNotSmsTagId],
        'track_tags_change' => 1,
        'is_only_add_tags' => 1,
      ]);
    }
  }

  return civicrm_api3_create_success(['message' => '"do not sms" is checked to selected contacts. To case has added "do not sms" tag.']);
}

function _civicrm_api3_supportcase_quick_action_apply_no_sms_spec(&$params) {
  $params['contact_ids'] = [
    'name' => 'contact_ids',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Contact ids',
  ];
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
