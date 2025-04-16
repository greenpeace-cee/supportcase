<?php

use CRM_Supportcase_ExtensionUtil as E;

function civicrm_api3_supportcase_manage_case_make_user_message($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }
  $manageCaseLink = CRM_Utils_System::url('civicrm/a/', NULL, TRUE, 'supportcase/manage-case/' . $params['case_id']);

  if ($params['type'] == 'resolve-case') {
    CRM_Core_Session::setStatus(
      E::ts(
        '
          <span>
            <span>You can view this case by link:</span>
            <a href="%1">Manage this Case</a>
          </span>
        ',
        [1 => $manageCaseLink]
      ),
      'Case is resolved!',
      'success'
    );
  } elseif ($params['type'] == 'report-spam') {
    CRM_Core_Session::setStatus(
      E::ts(
        '
          <span>
            <span>You can view this case by link:</span>
            <a href="%1">Manage this Case</a>
          </span>
        ',
        [1 => $manageCaseLink]
      ),
      'Case reported as spam!',
      'success'
    );
  } else {
    throw new api_Exception('Unknown message type', 'unknown_message_type');
  }

  return civicrm_api3_create_success(['message' => 'Message made successfully.']);
}

function _civicrm_api3_supportcase_manage_case_make_user_message_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
  $params['type'] = [
    'name' => 'type',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Message type',
  ];
}
