<?php

/**
 * Gets contact info
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_get_contact_info($params) {
  try {
    $contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $params['contact_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Contact does not exist.', 'contact_does_not_exist');
  }
  $tags = CRM_Supportcase_Utils_Tags::getTags($contact['contact_id'], 'civicrm_contact');
  foreach ($tags as $key => $tag) {
    if (empty($tag['color'])) {
      unset($tags[$key]);
    }
  }

  $contactInfo = [
    'display_name' => $contact['display_name'],
    'contact_id' => $contact['contact_id'],
    'email' => $contact['email'],
    'phone' => $contact['phone'],
    'birth_date' => $contact['birth_date'],
    'tags' => $tags,
    'is_has_bpk' => false,//TODO in future
    'link' => CRM_Utils_System::url('civicrm/contact/view/', [
      'reset' => '1',
      'cid' => $contact['contact_id'],
    ], FALSE, NULL, FALSE),
  ];

  return civicrm_api3_create_success([$contactInfo]);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_get_contact_info_spec(&$params) {
  $params['contact_id'] = [
    'name' => 'contact_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Contact id',
  ];
}
