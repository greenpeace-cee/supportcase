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
        'return' => ["value", "label"],
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

}
