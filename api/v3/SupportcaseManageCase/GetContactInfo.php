<?php

/**
 * Gets contact info
 */
function civicrm_api3_supportcase_manage_case_get_contact_info($params) {
  $returnFields = ['id', 'display_name', 'contact_id', 'email', 'phone', 'birth_date'];
  $bpkStatusFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::BPK_STATUS, CRM_Supportcase_Install_Entity_CustomGroup::BPK, TRUE);
  $bpkResolvedValue = CRM_Supportcase_Utils_OptionValue::getValue(CRM_Supportcase_Install_Entity_OptionGroup::BPK_STATUS, CRM_Supportcase_Install_Entity_OptionValue::BPK_STATUS_RESOLVED);
  if (!empty($bpkStatusFieldName) && !empty($bpkResolvedValue)) {
    $returnFields[] = $bpkStatusFieldName;
  }

  try {
    $contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $params['contact_id'],
      'return' => $returnFields,
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Contact does not exist.', 'contact_does_not_exist');
  }

  $isHasBpk = false;
  $tags = CRM_Supportcase_Utils_Tags::getTags($contact['contact_id'], 'civicrm_contact');
  foreach ($tags as $key => $tag) {
    if (empty($tag['color'])) {
      unset($tags[$key]);
    }
  }

  if (!empty($bpkStatusFieldName) && !empty($bpkResolvedValue) && !empty($contact[$bpkStatusFieldName])) {
    if ($contact[$bpkStatusFieldName] == $bpkResolvedValue) {
      $isHasBpk = true;
    }
  }

  $duplicateLinks = [];
  if (!empty($params['is_search_duplicates'])) {
    $duplicateLinks = CRM_Supportcase_Utils_DuplicateContacts::getData($contact['contact_id']);
  }

  $contactInfo = [
    'display_name' => $contact['display_name'],
    'contact_id' => $contact['contact_id'],
    'email' => $contact['email'],
    'phone' => $contact['phone'],
    'birth_date' => $contact['birth_date'],
    'tags' => $tags,
    'is_has_bpk' => $isHasBpk,
    'link' => CRM_Utils_System::url('civicrm/contact/view/', [
      'reset' => '1',
      'cid' => $contact['contact_id'],
    ], FALSE, NULL, FALSE),
    'duplicate_links' => $duplicateLinks,
  ];

  return civicrm_api3_create_success([$contactInfo]);
}

function _civicrm_api3_supportcase_manage_case_get_contact_info_spec(&$params) {
  $params['contact_id'] = [
    'name' => 'contact_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Contact id',
  ];
  $params['is_search_duplicates'] = [
    'name' => 'is_search_duplicates',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'title' => 'Is search duplicates?',
  ];
}
