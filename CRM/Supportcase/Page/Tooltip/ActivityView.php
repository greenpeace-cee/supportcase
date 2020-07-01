<?php

class CRM_Supportcase_Page_Tooltip_ActivityView extends CRM_Core_Page {

  public function run() {
    $activityId = CRM_Utils_Request::retrieve('id', 'Positive');
    if (empty($activityId)) {
      throw new Exception('Cannot find activity id.');
    }

    try {
      $activity = civicrm_api3('Activity', 'getsingle', ['id' => $activityId]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new Exception('Activity(id=' . $activityId . ') does not exist.');
    }

    if (!CRM_Activity_BAO_Activity::checkPermission($activityId, CRM_Core_Action::VIEW)) {
      throw new Exception('You do not have permission to view this page.');
    }

    $activityData = [
      'subject' => $activity['subject'],
      'client' => $activity['source_contact_sort_name'],
      'date' => $activity['activity_date_time'],
      'activity_type' => CRM_Supportcase_Utils_OptionValue::getLabelByValue($activity['activity_type_id'], 'activity_type'),
      'details' => $activity['details'],
      'status' => CRM_Supportcase_Utils_OptionValue::getLabelByValue($activity['status_id'], 'activity_status'),
      'priority' => CRM_Supportcase_Utils_OptionValue::getLabelByValue($activity['priority_id'], 'priority'),
      'location' => $activity['location'],
      'medium' => CRM_Supportcase_Utils_OptionValue::getLabelByValue($activity['medium_id'], 'encounter_medium'),
      'created_date' => $activity['created_date'],
      'modified_date' => $activity['modified_date'],
      'added_by_contacts' => CRM_Activity_BAO_ActivityContact::getNames($activityId, '2'),
      'assigned_to_contacts' => CRM_Activity_BAO_ActivityContact::getNames($activityId, '1'),
      'with_contacts' => CRM_Activity_BAO_ActivityContact::getNames($activityId, '3'),
    ];

    $this->assign('activity', $activityData);

    return parent::run();
  }

}
