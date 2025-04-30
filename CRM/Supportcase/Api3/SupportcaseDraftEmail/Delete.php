<?php

/**
 * Uses on 'SupportcaseDraftEmail->delete' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Delete extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult(): array {
    \Civi\Api4\MailutilsMessage::delete(FALSE)
      ->addWhere('id', '=', $this->params['mailutils_message_id'])
      ->setLimit(1)
      ->execute();

    civicrm_api3('Activity', 'delete', [
      'id' => $this->params['mailutils_message']['activity_id'],
    ]);

    \Civi\Api4\MailutilsMessageParty::delete(FALSE)
      ->addWhere('mailutils_message_id', '=', $this->params['mailutils_message_id'])
      ->execute();

    return [
      'message' => 'Draft deleted!',
      'activity_id' => $this->params['mailutils_message']['activity_id'],
      'mailutils_message_id' => $this->params['mailutils_message_id'],
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
    $mailutilsMessage = CRM_Supportcase_Utils_MailutilsMessage::getMailutilsMessageById($params['mailutils_message_id']);
    if (empty($mailutilsMessage)) {
      throw new api_Exception('Cannot find mailutils message id.', 'can_not_find_mailutils_message_id');
    }

    $contactId = CRM_Core_Session::getLoggedInContactID();
    if (empty($contactId)) {
      throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
    }

    if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($mailutilsMessage['case_id'], $contactId)) {
      throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
    }

    return [
      'mailutils_message_id' => (int) $params['mailutils_message_id'],
      'mailutils_message' => $mailutilsMessage,
    ];
  }

}
