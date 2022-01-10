<?php

/**
 * Uses on 'SupportcaseComment->update' api
 */
class CRM_Supportcase_Api3_SupportcaseComment_GetAll extends CRM_Supportcase_Api3_SupportcaseComment_Base {

  public function getResult() {
    try {
      $activity = civicrm_api3('Activity', 'get', [
        'case_id' => $this->params['case_id'],
        'activity_type_id' => CRM_Supportcase_Utils_ActivityType::NOTE,
        'options' => ['limit' => 0, 'sort' => "created_date DESC"],
        'return' => ["details", "activity_date_time", "modified_date", "created_date", "source_contact_id", 'source_contact_name'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error getting activity. Error message: ' . $e->getMessage(), 'error_getting_activity');
    }

    return $activity['values'];
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
