<?php

/**
 * Update case info
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_supportcase_manage_case_update_case_info($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  $updateCaseParams = [];

  //handles subject:
  if (isset($params['subject'] )) {
    if (!empty($params['subject'])) {
      $updateCaseParams['subject'] = $params['subject'];
    } else {
      throw new api_Exception('Subject cannot be empty.', 'subject_cannot_be_empty');
    }
  }

  //handles status_id:
  if (isset($params['status_id'] )) {
    if (!empty($params['status_id'])) {
      $updateCaseParams['status_id'] = $params['status_id'];
    } else {
      throw new api_Exception('Status cannot be empty.', 'status_id_cannot_be_empty');
    }
  }

  //handles start_date:
  if (isset($params['start_date'] )) {
    if (!empty($params['start_date'])) {
      $updateCaseParams['start_date'] = $params['start_date'];
    } else {
      throw new api_Exception('Start date cannot be empty.', 'start_date_cannot_be_empty');
    }
  }

  //handles new_case_client_id:
  if (isset($params['new_case_client_id'] )) {
    if (!empty($params['new_case_client_id'])) {
      try {
        $newClient = civicrm_api3('Contact', 'getsingle', [
          'id' => $params['new_case_client_id'],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        throw new api_Exception('Client id does not exist', 'client_id_does_not_exist');
      }

      $currentClientId = '';
      foreach ($case['client_id'] as $clientId) {
        $currentClientId = $clientId;
      }

      if ($currentClientId == $params['new_case_client_id']) {
        throw new api_Exception('New client id and current client id is the same.', 'the_same_current_client_id_and_new_client_id');
      }

      $updateCaseParams['client_id'] = $params['new_case_client_id'];
    } else {
      throw new api_Exception('Clients cannot be empty.', 'clients_cannot_be_empty');
    }
  }

  //handles category_id:
  if (!empty($params['category_id'])) {
    $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('category', CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
    if (!empty($categoryFieldName)) {
      $updateCaseParams[$categoryFieldName] = $params['category_id'];
    }
  }

  //handles tags_ids:
  if (isset($params['tags_ids']) && is_array($params['tags_ids'])) {
    CRM_Supportcase_Utils_Case::deleteAllTagsRelatedToCase($params['case_id']);

    $caseIds = [$params['case_id']];
    foreach ($params['tags_ids'] as $tagId) {
      if (CRM_Supportcase_Utils_Case::isCaseTagExist($tagId)) {
        CRM_Core_BAO_EntityTag::addEntitiesToTag($caseIds, $tagId, 'civicrm_case', FALSE);
      }
    }
  }

  if (!empty($updateCaseParams)) {
    $updateCaseParams['id'] = $params['case_id'];
    $case = civicrm_api3('Case', 'create', $updateCaseParams);
  }

  return civicrm_api3_create_success(['message' => 'Case is updated!', 'case' => $case]);
}

/**
 * This is used for documentation and validation.
 *
 * @param array $params description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_supportcase_manage_case_update_case_info_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['subject'] = [
    'name' => 'subject',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Subject',
  ];
  $params['status_id'] = [
    'name' => 'status_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Status id',
  ];
  $params['start_date'] = [
    'name' => 'start_date',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Start date',
  ];
  $params['category_id'] = [
    'name' => 'category_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Category id',
  ];
  $params['new_case_client_id'] = [
    'name' => 'new_case_client_id',
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'New case client id',
  ];
}
