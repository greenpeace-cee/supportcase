<?php

/**
 * Creates supportcase
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_create($params) {
  $categories = CRM_Supportcase_Utils_Category::getOptions();
  if (!empty($params['category_id']) && empty($categories[$params['category_id']])) {
    throw new CiviCRM_API3_Exception('Case category does not exist.', 'case_category_does_not_exist');
  }

  if (!empty($params['subject']) && strlen($params['subject']) > 255) {
    throw new CiviCRM_API3_Exception('To long subject. Type less than 255.', 'to_long_subject');
  }

  if (!empty($params['client_contact_id'])) {
    try {
      civicrm_api3('Contact', 'getsingle', [
        'id' => $params['client_contact_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new CiviCRM_API3_Exception('Contact does not exist.', 'contact_does_not_exist');
    }
  }

  $caseParams = [
    'contact_id' => $params['client_contact_id'],
    'case_type_id' => CRM_Supportcase_Install_Entity_CaseType::SUPPORT_CASE,
    'subject' => $params['subject'],
    'status_id' => "Open",
    'start_date' => "now",
    'medium_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_DAO_Activity', 'encounter_medium', 'email'),
  ];

  $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
  if (!empty($categoryFieldName)) {
    $caseParams[$categoryFieldName] = $params['category_id'];
  }

  $result = civicrm_api3('Case', 'create', $caseParams);

  return civicrm_api3_create_success($result);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_create_spec(&$params) {
  $params['client_contact_id'] = [
    'name' => 'client_contact_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Client contact id',
  ];
  $params['subject'] = [
    'name' => 'subject',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
  $params['category_id'] = [
    'name' => 'category_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Category',
  ];
}
