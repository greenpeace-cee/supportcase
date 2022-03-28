<?php

/**
 * Creates new Contact with entered email
 *
 * @param array $params
 *
 * @return array
 */
function  civicrm_api3_supportcase_email_create_new_contact_email($params) {
  if (!CRM_Supportcase_Utils_Email::isValidEmail($params['email'])) {
    throw new api_Exception('Please enter valid email', 'email_is_not_valid');
  }

  $createdContact = civicrm_api3('Contact', 'create', [
    'contact_type' => "Individual",
    'return' => ['id'],
    'email' => $params['email'],
  ]);

  // use this api to get email which created by Contact api
  $contact = civicrm_api3('Contact', 'getsingle', [
    'return' => ["email"],
    'id' => $createdContact['id'],
  ]);

  $newEmailLocationType = CRM_Supportcase_Install_Entity_LocationType::SUPPORT;
  if (!CRM_Supportcase_Utils_Email::isLocationTypeExist($newEmailLocationType)) {
    $newEmailLocationType = 'Main';
  }

  // update recently created(by Contact api) email fields:
  civicrm_api3('Email', 'create', [
    'id' => $contact['email_id'],
    'location_type_id' => $newEmailLocationType,
  ]);

  $emails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($contact['email_id']);
  if (empty($emails[0])) {
    throw new api_Exception('Something went wrong', 'something_went_wrong');
  }

  return civicrm_api3_create_success($emails[0], $params);
}


/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_email_create_new_contact_email_spec(&$params) {
  $params['email'] = [
    'name' => 'email',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,// use T_STRING because T_EMAIL does not validate field
    'title' => 'Email',
  ];
}
