<?php

/**
 * Uses on 'SupportcaseDraftEmail->send' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Send extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $this->changeActivityStatus(CRM_Supportcase_Utils_ActivityStatus::DRAFT_EMAIL);

    try {
      \Civi\Api4\MailutilsMessage::send(FALSE)
        ->setMessageId($this->params['mailutils_message_id'])
        ->execute();
    } catch (Exception $e) {
      $this->changeActivityStatus(CRM_Supportcase_Utils_ActivityStatus::SUPPORTCASE_DRAFT_EMAIL);
      throw new api_Exception('Error sending email. Error: ' . $e->getMessage(), 'cannot_send_email');
    }

    return [
      'message' => 'Email is sent.',
      'mailutils_message_id' => $this->params['mailutils_message_id'],
    ];
  }

  /**
   * @param $newStatus
   * @return void
   */
  protected function changeActivityStatus($newStatus) {
    try {
      civicrm_api3('Activity', 'create', [
        'id' => $this->params['mailutils_message']['activity_id'],
        'status_id' => $newStatus,
      ]);
    } catch (Exception $e) {
      throw new api_Exception('Error sending email. Error: ' . $e->getMessage(), 'cannot_send_email');
    }
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params): array {
    $mailutilsMessage = CRM_Supportcase_Utils_MailutilsMessage::getMailutilsMessageById($params['mailutils_message_id']);
    if (empty($mailutilsMessage)) {
      throw new api_Exception('Cannot find mailutils message id.', 'can_not_find_mailutils_message_id');
    }

    $contactId = CRM_Core_Session::getLoggedInContactID();
    if (empty($contactId)) {
      throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
    }

    $caseId = CRM_Supportcase_Utils_Activity::getCaseId($mailutilsMessage['activity_id']);
    if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($caseId, $contactId)) {
      throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
    }

    //TODO validate if activity has status SUPPORTCASE_DRAFT_EMAIL

    return [
      'mailutils_message_id' => (int) $params['mailutils_message_id'],
      'mailutils_message' => $mailutilsMessage,
      'case_id' => $caseId,
    ];
  }

}
