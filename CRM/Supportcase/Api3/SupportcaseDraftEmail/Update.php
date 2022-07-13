<?php

/**
 * Uses on 'SupportcaseDraftEmail->update' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Update extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $activityParams = [
      'id' => $this->params['mailutils_message']['activity_id'],
    ];

    if (!empty($this->params['subject'])) {
      $activityParams['subject'] = $this->params['subject'];
    }

    if (!empty($this->params['body'])) {
      $activityParams['details'] = $this->params['body'];
    }

    if (isset($this->params['to_email_ids'])) {
      CRM_Supportcase_Utils_MailutilsMessage::updateMessagePartyContactIds(
        $this->params['mailutils_message']['id'],
        $this->params['to_email_ids'],
        CRM_Supportcase_Utils_PartyType::TO
      );
    }

    if (isset($this->params['cc_email_ids'])) {
      CRM_Supportcase_Utils_MailutilsMessage::updateMessagePartyContactIds(
        $this->params['mailutils_message']['id'],
        $this->params['cc_email_ids'],
        CRM_Supportcase_Utils_PartyType::CC
      );
    }

    if (isset($this->params['from_email_ids'])) {
      CRM_Supportcase_Utils_MailutilsMessage::updateMessagePartyContactIds(
        $this->params['mailutils_message']['id'],
        $this->params['from_email_ids'],
        CRM_Supportcase_Utils_PartyType::FROM
      );
    }

    try {
      civicrm_api3('Activity', 'create', $activityParams);
    } catch (Exception $e) {
      throw new api_Exception('Error updating draft email. Error: ' . $e->getMessage(), 'error_updating_draft_email');
    }

    return [
      'message' => 'Draft updated!',
      'data' => $this->getUpdatedDraftEmailData(),
      'mailutils_message_id' => $this->params['mailutils_message']['id'],
    ];
  }

  /**
   * @return array
   */
  private function getUpdatedDraftEmailData(): array {
    $updatedDraftEmailData = civicrm_api3('SupportcaseDraftEmail', 'get', [
      'mailutils_message_id' => $this->params['mailutils_message_id'],
      'return' => $this->params['returnFields'],
    ]);

    if (!empty($updatedDraftEmailData['values'])) {
      return $updatedDraftEmailData['values'];
    }

    return [];
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

    $preparedParams = [];
    $preparedParams['mailutils_message_id'] = (int) $params['mailutils_message_id'];
    $preparedParams['mailutils_message'] = $mailutilsMessage;

    if (!empty($params['subject'])) {
      //TODO: validate
      $preparedParams['subject'] = $params['subject'];
    }

    if (!empty($params['body'])) {
      //TODO: validate
      $preparedParams['body'] = $params['body'];
    }

    if (isset($params['to_email_ids'])) {
      $preparedParams['to_email_ids'] = $params['to_email_ids'];
    }

    if (isset($params['cc_email_ids'])) {
      $preparedParams['cc_email_ids'] = $params['cc_email_ids'];
    }

    if (isset($params['from_email_ids'])) {
      $preparedParams['from_email_ids'] = $params['from_email_ids'];
    }

    $preparedParams['returnFields'] = $this->getReturnFields($params);

    return $preparedParams;
  }

}
