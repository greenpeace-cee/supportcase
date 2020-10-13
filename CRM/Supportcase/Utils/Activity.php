<?php

class CRM_Supportcase_Utils_Activity {

  /**
   * Gets list of related contacts to activity
   * (1 assignee, 2 creator, 3 focus or target)
   *
   * @param $activityId
   * @return array
   */
  public static function getRelatedContacts($activityId) {
    $assignee = [];
    $creator = [];
    $target = [];

    if (!empty($activityId)) {
      try {
        $activityContacts = civicrm_api3('ActivityContact', 'get', [
          'sequential' => 1,
          'return' => ["contact_id.display_name", "activity_id", "contact_id.id", "record_type_id"],
          'activity_id' => $activityId,
          'options' => ['limit' => 0],
        ]);
      } catch (CiviCRM_API3_Exception $e) {}

      if (!empty($activityContacts['values'])) {
        foreach ($activityContacts['values'] as $activityContact) {
          $contact = [
            'display_name' => $activityContact['contact_id.display_name'],
            'link' => CRM_Utils_System::url('civicrm/contact/view/', [
              'reset' => '1',
              'cid' => $activityContact['contact_id.id'],
            ])
          ];

          if ($activityContact['record_type_id'] == '1') {
            $assignee[] = $contact;
          } else if ($activityContact['record_type_id'] == '2') {
            $creator[] = $contact;
          } else if ($activityContact['record_type_id'] == '3') {
            $target[] = $contact;
          }
        }
      }
    }

    return [
      'assignee' => $assignee,
      'creator' => $creator,
      'target' => $target,
    ];
  }

}
