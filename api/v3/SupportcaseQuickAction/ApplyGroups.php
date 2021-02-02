<?php

/**
 * Updates contacts groups
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_quick_action_apply_groups($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  if (!isset($params['groups_data'])) {
    throw new api_Exception('"groups_data" is required field.', 'groups_data_is_required_field');
  }

  if (!is_array($params['groups_data'])) {
    throw new api_Exception('"groups_data" have to be array.', 'not_valid_data');
  }

  foreach ($params['groups_data'] as $groupData) {
    try {
      civicrm_api3('Contact', 'getsingle', ['id' => $groupData['contact_id']]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Contact does not exist.', 'contact_does_not_exist');
    }
    try {
      civicrm_api3('Group', 'getsingle', ['id' => $groupData['group_id']]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Group does not exist.', 'group_does_not_exist');
    }
  }

  foreach ($params['opt_out_data'] as $optOutData) {
    try {
      civicrm_api3('Contact', 'getsingle', ['id' => $optOutData['contact_id']]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Contact does not exist.', 'contact_does_not_exist');
    }
  }

  foreach ($params['groups_data'] as $groupData) {
    CRM_Supportcase_Utils_Group::updateContactGroup($groupData['contact_id'], $groupData['group_id'], $groupData['is_contact_in_group']);
  }

  if (CRM_Supportcase_Utils_Setting::isCaseToolsExtensionEnable()) {
    $tbdTagId = CRM_Supportcase_Utils_Tags::getTagId(CRM_Supportcase_Install_Entity_Tag::TBD);
    if (!empty($tbdTagId)) {
      civicrm_api3('Case', 'create', [
        'id' => $params['case_id'],
        'tags_ids' => [$tbdTagId],
        'track_tags_change' => 1,
        'is_only_add_tags' => 1,
      ]);
    }
  }

  foreach ($params['opt_out_data'] as $optOutData) {
    civicrm_api3('Contact', 'create', [
      'contact_id' => $optOutData['contact_id'],
      'is_opt_out' => (bool) $optOutData['is_opt_out'],
    ]);
  }

  return civicrm_api3_create_success(['message' => 'Groups is updated.']);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_quick_action_apply_groups_spec(&$params) {
  $params['groups_data'] = [
    'name' => 'groups_data',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Groups data',
  ];
  $params['opt_out_data'] = [
    'name' => 'opt_out_data',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Contact opt out data',
  ];
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
