<?php

/**
 * Get activities related to the case
 */
function civicrm_api3_supportcase_manage_case_get_activities($params) {
  try {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    throw new api_Exception('Case does not exist.', 'case_does_not_exist');
  }

  try {
    $activities = civicrm_api3('Activity', 'get', [
      'is_deleted' => "0",
      'options' => ['limit' => 0],
      'case_id' => $params['case_id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    $activities = [];
  }

  $preparedActivities = [];
  if (!empty($activities['values'])) {
    foreach ($activities['values'] as $activity) {
      $relatedContacts = CRM_Supportcase_Utils_Activity::getRelatedContacts($activity['id']);

      $preparedActivities[] = [
        'id' => $activity['id'],
        'subject' => $activity['subject'],
        'created_date' => $activity['created_date'],
        'status_id' => $activity['status_id'],
        'status' => CRM_Core_PseudoConstant::getLabel('CRM_Activity_BAO_Activity', 'status_id', $activity['status_id']),
        'activity_type' => CRM_Core_PseudoConstant::getLabel('CRM_Activity_BAO_Activity', 'activity_type_id', $activity['activity_type_id']),
        'activity_type_id' =>  $activity['activity_type_id'],
        'assignee_contacts' =>  $relatedContacts['assignee'],
        'creator_contacts' =>  $relatedContacts['creator'],
        'target_contacts' =>  $relatedContacts['target'],
        'action_links' => [
          [
            'label' => 'View',
            'url' => CRM_Utils_System::url('civicrm/case/activity/view/', [
              'reset' => '1',
              'caseid' => $params['case_id'],
              'aid' => $activity['id'],
              'cid' => CRM_Core_Session::getLoggedInContactID(),
            ])
          ],
          [
            'label' => 'Edit',
            'url' => CRM_Utils_System::url('civicrm/case/activity/', [
              'reset' => '1',
              'caseid' => $params['case_id'],
              'id' => $activity['id'],
              'action' => 'update',
              'cid' => CRM_Core_Session::getLoggedInContactID(),
            ])
          ]
        ],
      ];
    }
  }

  return civicrm_api3_create_success($preparedActivities);
}

function _civicrm_api3_supportcase_manage_case_get_activities_spec(&$params) {
  $params['case_id'] = [
    'name' => 'case_id',
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Case id',
  ];
}
