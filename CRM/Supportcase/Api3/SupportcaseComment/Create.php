<?php

/**
 * Uses on 'SupportcaseComment->create' api
 */
class CRM_Supportcase_Api3_SupportcaseComment_Create extends CRM_Supportcase_Api3_SupportcaseComment_Base {

  public function getResult(): array {
    try {
      $activity = civicrm_api3('Activity', 'create', [
        'source_contact_id' => (CRM_Core_Session::singleton())->get('userID'),
        'case_id' => $this->params['case_id'],
        'subject' => 'Note',
        'activity_type_id' => CRM_Supportcase_Utils_ActivityType::NOTE,
        'details' => $this->params['comment'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error creating activity. Error message: ' . $e->getMessage(), 'error_creating_activity');
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
      'comment' => $this->getValidatedComment($params['comment']),
    ];
  }

}
