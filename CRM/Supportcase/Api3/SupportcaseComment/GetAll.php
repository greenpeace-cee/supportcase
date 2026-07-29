<?php

/**
 * Uses on 'SupportcaseComment->update' api
 */
class CRM_Supportcase_Api3_SupportcaseComment_GetAll extends CRM_Supportcase_Api3_SupportcaseComment_Base {

  public function getResult(): array {
    try {
      $activities = civicrm_api3('Activity', 'get', [
        'case_id' => $this->params['case_id'],
        'activity_type_id' => CRM_Supportcase_Utils_ActivityType::NOTE,
        'is_current_revision' => 1,
        'options' => ['limit' => 0, 'sort' => "activity_date_time DESC"],
        'return' => ["details", "activity_date_time", "modified_date", "created_date", "source_contact_id", 'source_contact_name'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error getting activity. Error message: ' . $e->getMessage(), 'error_getting_activity');
    }

    foreach ($activities['values'] as $id => $activity) {
      $activities['values'][$id]['details'] = CRM_Utils_String::purifyHTML($activity['details']);
      $activities['values'][$id]['details_text'] = CRM_Utils_String::htmlToText($activity['details']);
    }

    return $activities['values'];
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params) {
    return [
      'case_id' => $this->getValidatedCaseId($params['case_id']),
    ];
  }

}
