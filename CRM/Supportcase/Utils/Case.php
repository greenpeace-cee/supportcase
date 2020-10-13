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

  /**
   * Is case's tag existence
   *
   * @param $caseTagId
   * @return bool
   */
  public static function isCaseTagExist($caseTagId) {
    if (empty($caseTagId)) {
      return false;
    }

    try {
      $tag = civicrm_api3('Tag', 'getsingle', [
        'used_for' => "Cases",
        'id' => $caseTagId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    return !empty($tag['id']);
  }

  /**
   * Remove all related tags to case
   *
   * @param $caseId
   */
  public static function deleteAllTagsRelatedToCase($caseId) {
    if (empty($caseId)) {
      return;
    }

    $tagParams = [
      'entity_table' => 'civicrm_case',
      'entity_id' => $caseId,
    ];

    CRM_Core_BAO_EntityTag::del($tagParams);
  }

}
