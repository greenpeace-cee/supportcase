<?php

/**
 * Uses on 'SupportcaseDraftEmail->get' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_ByMailUtilsMessageId extends CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $draftEmails = [];

    try {
      $activity = civicrm_api3('Activity', 'getsingle', [
        'id' => $this->params['mailutils_message']['activity_id'],
        'api.Attachment.get' => [],
        'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details', 'status_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $draftEmails;
    }

    return $this->prepareDraftActivity($activity, $this->params['mailutils_message_id']);
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params) {
    $mailutilsMessage = CRM_Supportcase_Utils_MailutilsMessage::getMailutilsMessageById($params['mailutils_message_id']);
    if (empty($mailutilsMessage)) {
      throw new api_Exception('Cannot find mailutils message id.', 'can_not_find_mailutils_message_id');
    }

    $caseId = CRM_Supportcase_Utils_Activity::getCaseId($mailutilsMessage['activity_id']);

    return [
      'case_id' => $caseId,
      'case_category_id' => $this->getCaseCategoryId($caseId),
      'mailutils_message_id' => (int) $params['mailutils_message_id'],
      'mailutils_message' => $mailutilsMessage,
      'returnFields' => $this->getReturnFields($params),
    ];
  }

}
