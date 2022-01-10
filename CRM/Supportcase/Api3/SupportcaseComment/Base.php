<?php

/**
 * Methods for validation 'SupportcaseComment->*' apis
 */
class CRM_Supportcase_Api3_SupportcaseComment_Base extends CRM_Supportcase_Api3_Base {

  /**
   * Gets validated case id
   *
   * @param $caseId
   * @return int
   */
  protected function getValidatedCaseId($caseId) {
    try {
      civicrm_api3('Case', 'getsingle', [
        'id' => $caseId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    return $caseId;
  }

  /**
   * Gets validated comment
   *
   * @param $comment
   * @return string
   */
  protected function getValidatedComment($comment) {
    $validatedComment = CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($comment))));
    if (empty($validatedComment)) {
      throw new api_Exception('Empty comment', 'empty_comment');
    }

    return $validatedComment;
  }

  /**
   * Gets validated activity id
   *
   * @param $caseId
   * @return int
   */
  protected function getValidatedActivityId($activityId, $caseId) {
    try {
      civicrm_api3('Activity', 'getsingle', [
        'id' => $activityId,
        'case_id' => $caseId,
        'activity_type_id' => CRM_Supportcase_Utils_ActivityType::NOTE,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Activity does not exist in case.', 'activity_does_not_exist_in_case');
    }

    return $activityId;
  }

}
