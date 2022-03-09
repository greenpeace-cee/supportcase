<?php

/**
 * Uses on 'SupportcaseManageCase->send_email' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_SendEmail extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    $activityId = $this->createActivity();
    try {
      $this->attachFiles($activityId);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error saving files: Error: ' . $e->getMessage(), 'error_saving_files');
    }

    if ($this->params['email']['mode'] == 'forward') {
      CRM_Supportcase_Utils_Activity::copyAttachment($this->params['email']['options']['email_activity_id'], $activityId, $this->params['email']['forwardFileIds']);
    }

    $mailutilsMessage = $this->createMailutilsMessage($activityId);

    foreach ($this->params['email']['toEmails'] as $emailData) {
      $this->createMailutilsMessageParty($emailData, $mailutilsMessage['id'], CRM_Supportcase_Utils_PartyType::getToPartyTypeId());
    }

    foreach ($this->params['email']['fromEmails'] as $emailData) {
      $this->createMailutilsMessageParty($emailData, $mailutilsMessage['id'], CRM_Supportcase_Utils_PartyType::getFromPartyTypeId());
    }

    foreach ($this->params['email']['ccEmails'] as $emailData) {
      $this->createMailutilsMessageParty($emailData, $mailutilsMessage['id'], CRM_Supportcase_Utils_PartyType::getCcPartyTypeId());
    }

    try {
      \Civi\Api4\MailutilsMessage::send(FALSE)
        ->setMessageId($mailutilsMessage['id'])
        ->execute();
    } catch (Exception $e) {
      throw new api_Exception('Error sending email. Error: ' . $e->getMessage(), 'cannot_send_email');
    }

    return [
      'message' => 'Email is sent.',
      'activity_id' => $activityId,
      'mailutils_message_id' => $mailutilsMessage['id']
    ];
  }

  /**
   * Creates main activity
   * @return int
   */
  private function createActivity() {
    $targetContactIds = [];

    foreach ($this->params['email']['toEmails'] as $emailData) {
      $targetContactIds[] = $emailData['contact_id'];
    }
    foreach ($this->params['email']['ccEmails'] as $emailData) {
      if (!in_array($emailData['contact_id'], $targetContactIds)) {
        $targetContactIds[] = $emailData['contact_id'];
      }
    }

    try {
      $activity = civicrm_api3('Activity', 'create', [
        'source_contact_id' => $this->params['email']['fromEmails'][0]['contact_id'],
        'assignee_id' => 'user_contact_id',
        'activity_type_id' => 'Email',
        'subject' => $this->params['email']['subject'],
        'details' => $this->params['email']['body'],
        'status_id' => CRM_Supportcase_Utils_ActivityStatus::DRAFT_EMAIL,
        'target_id' => $targetContactIds,// cc + to contacts - the same logic as in core
        'case_id' => $this->params['caseId'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Cannot create Activity. Error: ' . $e->getMessage(), 'cannot_create_mailutils_message');
    }

    return $activity['id'];
  }

  /**
   * @param $activityId
   * @return array
   */
  private function attachFiles($activityId) {
    $results = [];

    if (empty($_FILES['attachments'])) {
      return $results;
    }

    foreach ($_FILES['attachments']['name'] as $key => $name) {
      $params = [
        'name' => $_FILES['attachments']['name'][$key],
        'mime_type' => $_FILES['attachments']['type'][$key],
        'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
        'entity_table' => 'civicrm_activity',
        'entity_id' => $activityId,
        'description' => '',
        'version' => 3,
        'check_permissions' => 1,
        'content' => '',
      ];

      CRM_Core_Transaction::create(TRUE)->run(function (CRM_Core_Transaction $tx) use ($params, $key, &$results) {
        $results[$key] = civicrm_api('Attachment', 'create', $params);

        if ($results[$key]['is_error'] != 1) {
          $moveResult = civicrm_api('Attachment', 'create', [
            'id' => $results[$key]['id'],
            'version' => 3,
            'options.move-file' => $params['tmp_name'],
          ]);
          if ($moveResult['is_error']) {
            $results[$key] = $moveResult;
            $tx->rollback();
          }
        }
      });
    }

    return $results;
  }

  /**
   * Creates MailutilsMessage entity
   *
   * @param $activityId
   * @return int
   */
  private function createMailutilsMessage($activityId) {
    $emailMessageId = \ezcMailTools::generateMessageId($this->params['email']['fromEmails'][0]['email']);
    $headers = '{}';
    $inReplyTo = NULL;

    if (in_array($this->params['email']['mode'], [CRM_Supportcase_Utils_Email::FORWARD_MODE, CRM_Supportcase_Utils_Email::REPLY_ALL_MODE, CRM_Supportcase_Utils_Email::REPLY_MODE])) {
      $headers = $this->generateHeaders($this->params['email']['options']['mailutils_thread_id'], $emailMessageId);
      $inReplyTo = $this->params['email']['options']['message_id'];
    }

    try {
      $mailutilsMessage = \Civi\Api4\MailutilsMessage::create(FALSE)
        ->addValue('activity_id', $activityId)
        ->addValue('subject', $this->params['email']['subject'])
        ->addValue('body', $this->params['email']['body'])
        ->addValue('mailutils_thread_id', $this->params['email']['options']['mailutils_thread_id'])
        ->addValue('mail_setting_id', $this->params['email']['options']['mail_setting_id'])
        ->addValue('in_reply_to', $inReplyTo)
        ->addValue('message_id', $emailMessageId)
        ->addValue('headers', $headers)
        ->execute()
        ->first();
    } catch (Exception $e) {
      throw new api_Exception('Cannot create "MailutilsMessage". Error: ' . $e->getMessage(), 'cannot_create_mailutils_message');
    }

    return $mailutilsMessage;
  }

  /**
   * Creates MailutilsMessageParty entity
   *
   * @return int
   */
  private function createMailutilsMessageParty($emailData, $mailutilsMessageId, $partyTypeId) {
    try {
      $messageParty = \Civi\Api4\MailutilsMessageParty::create(FALSE)
        ->addValue('mailutils_message_id', $mailutilsMessageId)
        ->addValue('contact_id', $emailData['contact_id'])
        ->addValue('party_type_id', $partyTypeId)
        ->addValue('name', $emailData['contact_display_name'])
        ->addValue('email', $emailData['email'])
        ->execute()
        ->first();
    } catch (Exception $e) {
      throw new api_Exception('Cannot create "MailutilsMessageParty". Error: ' . $e->getMessage(), 'cannot_create_mailutils_message_party');
    }

    return $messageParty;
  }

  /**
   * @param $mailutilsThreadId
   * @param $emailMessageId
   * @return string
   */
  private function generateHeaders($mailutilsThreadId, $emailMessageId) {
    $referenceList = [$emailMessageId];
    if (empty($mailutilsThreadId)) {
      return json_encode([
        "References" => '<' . implode('> <', $referenceList) . '>',
      ]);
    }

    $mailutilsMessages = \Civi\Api4\MailutilsMessage::get(FALSE)
      ->addSelect('*')
      ->addWhere('mailutils_thread_id', '=', $mailutilsThreadId)
      ->execute();
    foreach ($mailutilsMessages as $item) {
      $referenceList[] = $item['message_id'];
    }

    return json_encode([
      "References" => '<' . implode('> <', $referenceList) . '>',
    ]);
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   * @throws \api_Exception
   */
  protected function prepareParams($params) {
    if (!CRM_Supportcase_Utils_Setting::isMailUtilsExtensionEnable()) {
      throw new api_Exception('Cannot send email. Please install "mailutils" extension.', 'can_not_send_email_mailutils_extension_is_required');
    }

    if (empty($params['mode']) || !in_array($params['mode'], CRM_Supportcase_Utils_Email::getAvailableModes())) {
      throw new api_Exception('Error. Invalid mode. Mode can be: "' . implode('" , "', CRM_Supportcase_Utils_Email::getAvailableModes()) . '"', 'error_invalid_mode');
    }

    $case = new CRM_Case_BAO_Case();
    $case->id = $params['case_id'];
    $caseExistence = $case->find(TRUE);
    if (empty($caseExistence)) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    $attachmentsLimit = CRM_Supportcase_Utils_Setting::getActivityAttachmentLimit();
    if (!empty($_FILES['attachments']['name']) && count($_FILES['attachments']['name']) > $attachmentsLimit) {
      throw new api_Exception('To match attachments.Maximum is ' . $attachmentsLimit . '.', 'to_match_attachments');
    }

    //TODO: check by size? $maxFileSize = CRM_Supportcase_Utils_Setting::getMaxFilesSize();

    $contactId = CRM_Core_Session::getLoggedInContactID();
    if (empty($contactId)) {
      throw new api_Exception('Cannot find contact id.', 'can_not_find_contact_id');
    }

    if (CRM_Supportcase_BAO_CaseLock::isCaseLockedForContact($params['case_id'], $contactId)) {
      throw new api_Exception('The case is locked by another user.', 'case_locked_by_another_user');
    }

    $toEmails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($params['to_email_id']);
    if (empty($toEmails)) {
      throw new api_Exception('Cannot find to email', 'cannot_find_to_email');
    }

    $fromEmails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($params['from_email_id']);
    if (empty($fromEmails)) {
      throw new api_Exception('Cannot find from email', 'cannot_find_from_email');
    }

    $ccEmails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($params['cc_email_ids']);
    $bodyText = CRM_Utils_String::htmlToText($params['body']);

    if (strlen($params['subject']) > 255) {
      throw new api_Exception('Subject have to be less than 255 char', 'subject_to_long');
    }

    if (in_array($params['mode'], [CRM_Supportcase_Utils_Email::FORWARD_MODE, CRM_Supportcase_Utils_Email::REPLY_MODE, CRM_Supportcase_Utils_Email::REPLY_ALL_MODE])) {
      if (empty($params['email_activity_id'])) {
        throw new api_Exception('"email_activity_id" field is required.' , 'email_activity_id_is_required');
      }

      try {
        $emailActivity = civicrm_api3('Activity', 'getsingle', [
          'id' => $params['email_activity_id'],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        throw new api_Exception('Email activity does not exist.' , 'email_activity_does_not_exist');
      }

      $mailutilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($emailActivity['id']);
      if (empty($mailutilsMessage)) {
        throw new api_Exception('Cannot find related to activity MailutilsMessage.', 'cannot_find_mailutils_message');
      }

      $options = [
        'mailutils_thread_id' => $mailutilsMessage['mailutils_thread_id'],
        'mail_setting_id' => $mailutilsMessage['mail_setting_id'],
        'message_id' => $mailutilsMessage['message_id'],
        'email_activity_id' => $emailActivity['id'],
      ];
    } else {
      $options = [
        'mailutils_thread_id' => $this->generateMailutilsThreadId(),
        'mail_setting_id' => $this->generateMailSettingId(),
        'message_id' => null,// it is always null, it is reply/forward email 'message_id'
        'email_activity_id' => null,// it is always null, it is reply/forward email 'activity id'
      ];
    }

    return [
      'email' => [
        'toEmails' => $toEmails,
        'mode' => $params['mode'],
        'fromEmails' => $fromEmails,
        'ccEmails' => $ccEmails,
        'body' => json_encode([
          'html' => $params['body'],
          'text' => $bodyText,
        ]),
        'subject' => $params['subject'],
        'options' => $options,
        'forwardFileIds' => !empty($params['forward_file_ids']) ? $params['forward_file_ids'] : null,
      ],
      'caseId' => $params['case_id'],
    ];
  }

  /**
   * @return int
   */
  private function generateMailutilsThreadId() {
    $mailutilsThread = \Civi\Api4\MailutilsThread::create(FALSE)->execute()->first();

    return $mailutilsThread['id'];
  }

  /**
   * Gets mails setting id by air ar space
   * // TODO: sure that is correct id
   *
   * @return int
   */
  private function generateMailSettingId() {
    $mailSettings = \Civi\Api4\MailSettings::get(FALSE)
      ->addSelect('MAX(id) AS max_id')
      ->setLimit(0)
      ->execute();

    foreach ($mailSettings as $mailSetting) {
      return $mailSetting['max_id'];
    }

    throw new api_Exception('Error getting "MailSettings"' , 'error_getting_mail_setting_id');
  }

}
