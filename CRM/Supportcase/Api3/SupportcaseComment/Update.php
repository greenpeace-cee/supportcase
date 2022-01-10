<?php

/**
 * Uses on 'SupportcaseComment->update' api
 */
class CRM_Supportcase_Api3_SupportcaseComment_Update extends CRM_Supportcase_Api3_SupportcaseComment_Base {

  public function getResult() {
    try {
      $activity = civicrm_api3('Activity', 'create', [
        'id' => $this->params['activity_id'],
        'details' => $this->params['comment'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error updating activity. Error message: ' . $e->getMessage(), 'error_updating_activity');
    }

    return [
      'id' => $activity['id']
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
      'activity_id' => $this->getValidatedActivityId($params['activity_id'], $params['case_id']),
      'comment' => $this->getValidatedComment($params['comment']),
    ];
  }

}
