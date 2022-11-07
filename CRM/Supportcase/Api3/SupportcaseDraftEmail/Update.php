<?php

/**
 * Uses on 'SupportcaseDraftEmail->update' api
 */
class CRM_Supportcase_Api3_SupportcaseDraftEmail_Update extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $activityParams = [];
    $mailutilsMessageParams = [];

    if (isset($this->params['subject'])) {
      $activityParams['subject'] = $this->params['subject'];
      $mailutilsMessageParams['subject'] = $this->params['subject'];
      $mailutilsMessageParams['subject_normalized'] = CRM_Supportcase_Utils_Email::normalizeEmailSubject($this->params['subject']);
    }

    if (isset($this->params['body'])) {
      $jsonBody = json_encode(['html' => $this->params['body'], 'text' => CRM_Utils_String::htmlToText($this->params['body'])]);
      $activityParams['details'] = $jsonBody;
      $mailutilsMessageParams['body'] = $jsonBody;
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
      $fromEmail = '';

      if (empty($this->params['from_email_ids'])) {
        $activityParams['source_contact_id'] = $this->params['logged_in_contact_id'];
        $fromEmail = $this->getFirstEmailByContactId($this->params['logged_in_contact_id']);
      } else {
        $emails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($this->params['from_email_ids']);
        if (!empty($emails)) {
          $activityParams['source_contact_id'] = $emails[0]['contact_id'];
          $fromEmail = $emails[0]['email'];
        }
      }

      $emailMessageId = CRM_Supportcase_Utils_MailutilsMessage::generateMessageId($fromEmail);
      $headers = CRM_Supportcase_Utils_MailutilsMessage::generateHeaders($this->params['mailutils_message']['mailutils_thread_id'], $emailMessageId);
      $mailutilsMessageParams['message_id'] = $emailMessageId;
      $mailutilsMessageParams['headers'] = $headers;

      CRM_Supportcase_Utils_MailutilsMessage::updateMessagePartyContactIds(
        $this->params['mailutils_message']['id'],
        $this->params['from_email_ids'],
        CRM_Supportcase_Utils_PartyType::FROM
      );
    }

    $this->updateActivity($activityParams);
    $this->updateMailutilsMessage($mailutilsMessageParams);

    return [
      'message' => 'Draft updated!',
      'data' => $this->getUpdatedDraftEmailData(),
      'mailutils_message_id' => $this->params['mailutils_message']['id'],
    ];
  }

  /**
   * @param array $params
   * @return void
   */
  private function updateActivity(array $params) {
    if (empty($params)) {
      return;
    }

    $params['id'] = $this->params['mailutils_message']['activity_id'];
    try {
      civicrm_api3('Activity', 'create', $params);
    } catch (Exception $e) {
      throw new api_Exception('Error updating draft email. Error: ' . $e->getMessage(), 'error_updating_draft_email');
    }
  }

  /**
   * @param array $params
   * @return void
   */
  private function updateMailutilsMessage(array $params) {
    if (empty($params)) {
      return;
    }

    $mailutilsMessage = \Civi\Api4\MailutilsMessage::update(FALSE);

    $mailutilsMessage->addWhere('id', '=', $this->params['mailutils_message']['id']);

    foreach ($params as $name => $value) {
      $mailutilsMessage->addValue($name, $value);
    }

    $mailutilsMessage->execute();
  }

  /**
   * @return array
   */
  private function getActivity(): array {
    try {
      $activity = civicrm_api3('Activity', 'getsingle', [
        'id' => $this->params['mailutils_message']['activity_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Email activity does not exist.' , 'from_activity_does_not_exist');
    }

    return $activity;
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

    $preparedParams = [];
    $contactId = CRM_Core_Session::getLoggedInContactID();
    $preparedParams['logged_in_contact_id'] = $contactId;
    if (empty($contactId)) {
      throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
    }

    $caseId = CRM_Supportcase_Utils_Activity::getCaseId($mailutilsMessage['activity_id']);
    $preparedParams['case_id'] = $caseId;

    if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($caseId, $contactId)) {
      throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
    }

    $preparedParams['mailutils_message_id'] = (int) $params['mailutils_message_id'];
    $preparedParams['mailutils_message'] = $mailutilsMessage;

    //TODO: validate?
    if (isset($params['subject'])) {
      $preparedParams['subject'] = empty($params['subject']) ? '' : $params['subject'];
    }

    //TODO: validate?
    if (isset($params['body'])) {
      $preparedParams['body'] = empty($params['body']) ? '' : $params['body'];
    }

    if (isset($params['to_email_ids'])) {
      $preparedParams['to_email_ids'] = empty($params['to_email_ids']) ? '' : $params['to_email_ids'];
    }

    if (isset($params['cc_email_ids'])) {
      $preparedParams['cc_email_ids'] = empty($params['cc_email_ids']) ? '' : $params['cc_email_ids'];
    }

    if (isset($params['from_email_ids'])) {
      $preparedParams['from_email_ids'] = empty($params['from_email_ids']) ? '' : $params['from_email_ids'];
    }

    $preparedParams['returnFields'] = $this->getReturnFields($params);

    return $preparedParams;
  }

  /**
   * @param $contactId
   * @return string
   */
  private function getFirstEmailByContactId($contactId) {
    if (empty($contactId)) {
      return '';
    }

    try {
      $emails = civicrm_api3('Email', 'get', [
        'contact_id' => $contactId,
        'options' => ['limit' => 1],
      ]);
    } catch (Exception $e) {
      throw new api_Exception('Error updating draft email. Error: ' . $e->getMessage(), 'error_updating_draft_email');
    }

    foreach ($emails['values'] as $email) {
      return $email['email'];
    }

    return '';
  }

}
