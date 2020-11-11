<?php

class CRM_Supportcase_Utils_Case {

  /**
   * Gets all active case statuses
   *
   * @return array
   */
  public static function getCaseStatuses() {
    try {
      $caseStatus = civicrm_api3('OptionValue', 'get', [
        'return' => ["value", "label", "name"],
        "sequential" => 1,
        'option_group_id' => 'case_status',
        'is_active' => 1,
        'options' => [
          'limit' => 0,
          'sort' => 'label'
        ]
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return (!empty($caseStatus['values'])) ? $caseStatus['values'] : [];
  }

  /**
   * Finds in activities phone number
   *
   * @param $caseId
   * @return string
   */
  public static function findPhoneNumberInActivities($caseId) {
    try {
      $activities = civicrm_api3('Activity', 'get', [
        'sequential' => 1,
        'return' => ["phone_number"],
        'case_id' => $caseId,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return '';
    }

    $phoneNumber = '';
    if (!empty($activities['values'])) {
      foreach ($activities['values'] as $activity) {
        if (!empty($activity["phone_number"])) {
          $phoneNumber = $activity["phone_number"];
          break;
        }
      }
    }

    return $phoneNumber;
  }

}
