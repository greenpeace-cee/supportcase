<?php

/**
 * Uses on 'SupportcaseManageCase->send_email' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_SendEmail extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    //TODO: Do we need to create activity by CRM_Activity_BAO_Activity::createEmailActivity?
    //todo: create a activity
    $mailutilsMessage = $this->createMailutilsMessage();

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
      \Civi\Api4\MailutilsMessage::send()
        ->setMessageId($mailutilsMessage['id'])
        ->execute();
    } catch (Exception $e) {
      throw new api_Exception('Error sending email. Error: ' . $e->getMessage(), 'cannot_send_email');
    }

    return ['message' => 'Email is sent.', 'case' => $this->params['case']];
  }

  /**
   * Creates MailutilsMessage entity
   *
   * @return int
   */
  private function createMailutilsMessage() {
    //TODO: if exist get thread id from previous email
    try {
      $mailutilsThread = \Civi\Api4\MailutilsThread::create()->execute()->first();
    } catch (Exception $e) {
      throw new api_Exception('Cannot create "MailutilsThread". Error: ' . $e->getMessage(), 'cannot_create_mailutils_thread');
    }

    try {
      $mailutilsMessage = \Civi\Api4\MailutilsMessage::create()
        ->addValue('activity_id', $this->params['email']['activity']['id'])
        ->addValue('subject', $this->params['email']['subject'])
        ->addValue('body', $this->params['email']['body'])
        ->addValue('mailutils_thread_id', $mailutilsThread['id'])
        ->addValue('mail_setting_id', '1')//TODO: remove dummy data/// mail_setting_id id prev
        ->addValue('message_id', 'TODO')//TODO: remove dummy data // this remove in future
        ->addValue('in_reply_to', 'TODO')//TODO: remove dummy data /// $mailutilsMessage id prev
        ->addValue('headers', 'TODO')//TODO: remove dummy data
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
      $messageParty = \Civi\Api4\MailutilsMessageParty::create()
          ->addValue('mailutils_message_id', $mailutilsMessageId)
          ->addValue('contact_id', $emailData['contact_id'])
          ->addValue('party_type_id', $partyTypeId)
          ->addValue('name', CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($emailData['contact_display_name'], $emailData['email']))
          ->addValue('email', $emailData['email'])
          ->execute()
          ->first();
    } catch (Exception $e) {
      throw new api_Exception('Cannot create "MailutilsMessageParty". Error: ' . $e->getMessage(), 'cannot_create_mailutils_message_party');
    }

    return $messageParty;
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

    try {
      $case = civicrm_api3('Case', 'getsingle', [
        'id' => $params['case_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

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

    try {
      $emailActivity = civicrm_api3('Activity', 'getsingle', [
        'id' => $params['email_activity_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Email activity does not exist.' , 'email_activity_does_not_exist');
    }

    $ccEmails = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($params['cc_email_ids']);
    $bodyText = CRM_Utils_String::htmlToText($params['body']);

    if (strlen($params['subject']) > 255) {
      throw new api_Exception('Subject have to be less than 255 char', 'subject_to_long');
    }

    return [
      'email' => [
        'toEmails' => $toEmails,
        'fromEmails' => $fromEmails,
        'ccEmails' => $ccEmails,
        'body' => $bodyText,
        'activity' => $emailActivity,
        'subject' => $params['subject'],
      ],
      'case' => $case,
      'caseId' => $params['case_id'],
    ];
  }

}
