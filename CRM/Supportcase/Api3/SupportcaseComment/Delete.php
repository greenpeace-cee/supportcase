<?php

/**
 * Uses on 'SupportcaseComment->delete' api
 */
class CRM_Supportcase_Api3_SupportcaseComment_Delete extends CRM_Supportcase_Api3_SupportcaseComment_Base {

  public function getResult(): array {
    try {
      civicrm_api3('Activity', 'delete', [
        'id' => $this->params['activity_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error deleting activity. Error message: ' . $e->getMessage(), 'error_deleting_activity');
    }

    return [
      'message' => 'Activity is deleted.'
    ];
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
      'activity_id' => $this->getValidatedActivityId($params['id'], $params['case_id']),
    ];
  }

}
