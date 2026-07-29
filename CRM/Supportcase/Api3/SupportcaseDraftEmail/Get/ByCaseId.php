<?php

/**
 * Uses on 'SupportcaseDraftEmail->get' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_ByCaseId extends CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_Base {

  /**
   * Get results of api
   */
  public function getResult(): array {
    $draftEmails = [];

    try {
      $activities = civicrm_api3('Activity', 'get', [
        'is_deleted' => '0',
        'case_id' => $this->params['case_id'],
        'activity_type_id' => ['IN' => ['Email', 'Inbound Email']],
        'api.Attachment.get' => [],
        'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details', 'status_id'],
        'options' => ['sort' => 'activity_date_time DESC', 'limit' => 0],
        'status_id' => CRM_Supportcase_Utils_ActivityStatus::SUPPORTCASE_DRAFT_EMAIL,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $draftEmails;
    }

    if (!empty($activities['values'])) {
      foreach ($activities['values'] as $activity) {
        $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($activity['id']);
        if (empty($mailUtilsMessage)) {
          throw new api_Exception('Error. Cannot get MailUtilsMessage for activity id=' . $activity['id'], 'error_getting_mailutils_message');
        }

        try {
          $draftEmails[] = $this->prepareDraftActivity($activity, $mailUtilsMessage['id']);
        } catch (CiviCRM_API3_Exception $e) {
          throw new api_Exception('Error. Cannot get draft email activity data id=' . $activity['id'], 'error_getting_draft_email_activity_data');
        }
      }
    }

    return $draftEmails;
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params): array {
    $case = $this->getCase($params['case_id']);

    return [
      'case_id' => (int) $params['case_id'],
      'case' => (int) $case,
      'case_category_id' => $case['case_category_id'],
      'returnFields' => $this->getReturnFields($params),
    ];
  }

}
