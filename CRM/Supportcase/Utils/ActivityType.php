<?php

class CRM_Supportcase_Utils_ActivityType {

  /**
   * @param $activityTypeId
   * @return string
   */
  public static function getLabelById($activityTypeId) {
    if (empty($activityTypeId)) {
      return '';
    }

    try {
      $activityTypeLabel = civicrm_api3('OptionValue', 'getvalue', [
        'return' => "label",
        'option_group_id' => "activity_type",
        'value' => $activityTypeId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return '';
    }

    return $activityTypeLabel;
  }

}
