<?php

/**
 * Finds contacts by phone number
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_apply_no_sms($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  $doNotSmsTagId = CRM_Supportcase_Utils_Tags::getTagId(CRM_Supportcase_Install_Entity_Tag::DO_NOT_SMS);
  if (!empty($doNotSmsTagId)) {
    $entityIds = [$params['case_id']];
    CRM_Core_BAO_EntityTag::addEntitiesToTag($entityIds, $doNotSmsTagId, 'civicrm_case', FALSE);
  }

  foreach ($params['contact_ids'] as $contactId) {
    civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'do_not_sms' => 1,
    ]);
  }

  return civicrm_api3_create_success(['message' => '"do not sms" is checked to selected contacts. To case has added "do not sms" tag.']);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
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
