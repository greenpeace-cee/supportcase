<?php

/**
 * Uses on 'SupportcaseManageCase->get_email_activities' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_GetEmailActivities extends CRM_Supportcase_Api3_Base {

  /**
   * Get results of api
   */
  public function getResult() {
    try {
      $activities = civicrm_api3('Activity', 'get', [
        'is_deleted' => '0',
        'case_id' => $this->params['case_id'],
        'activity_type_id' => ['IN' => ['Email', 'Inbound Email']],
        'api.Attachment.get' => [],
        'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details'],
        'api.ActivityContact.get' => ['record_type_id' => 'Activity Assignees', 'return' => 'contact_id.display_name'],
        'options' => ['sort' => 'activity_date_time DESC', 'limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    $preparedActivities = [];
    if (!empty($activities['values'])) {
      foreach ($activities['values'] as $activity) {
        $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($activity['id']);
        if (empty($mailUtilsMessage)) {
          throw new api_Exception('Error. Cannot get MailUtilsMessage for activity id=' . $activity['id'], 'error_getting_mailutils_message');
        }

        $mainEmailId =  CRM_Supportcase_Utils_Activity::getMainEmailId($mailUtilsMessage['mail_setting_id']);
        if (empty($mainEmailId)) {
          throw new api_Exception('Error. Cannot get main email for activity id=' . $activity['id'], 'error_getting_main_email');
        }

        try {
          $preparedActivities[] = $this->prepareActivity($activity, $mailUtilsMessage, $mainEmailId);
        } catch (CiviCRM_API3_Exception $e) {
          throw new api_Exception('Error. Cannot get email activity data id=' . $activity['id'], 'error_getting_email_activity_data');
        }
      }
    }

    return $preparedActivities;
  }

  /**
   * @param $activity
   * @return array
   */
  private function prepareActivity($activity, $mailUtilsMessage, $mainEmailId) {
    $ccPartyTypeId = CRM_Supportcase_Utils_PartyType::getCcPartyTypeId();
    $toPartyTypeId = CRM_Supportcase_Utils_PartyType::getToPartyTypeId();
    $fromPartyTypeId = CRM_Supportcase_Utils_PartyType::getFromPartyTypeId();
    $ccMessageParties = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessageParties($mailUtilsMessage['id'], $ccPartyTypeId);
    $toMessageParties = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessageParties($mailUtilsMessage['id'], $toPartyTypeId);
    $fromMessageParties = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessageParties($mailUtilsMessage['id'], $fromPartyTypeId);
    $ccEmailsData = $this->prepareEmailsData($ccMessageParties);
    $toEmailsData = $this->prepareEmailsData($toMessageParties);
    $fromEmailsData = $this->prepareEmailsData($fromMessageParties);
    $attachmentsLimit = CRM_Supportcase_Utils_Setting::getActivityAttachmentLimit();
    $maxFileSize = CRM_Supportcase_Utils_Setting::getMaxFilesSize();
    $normalizedSubject = CRM_Supportcase_Utils_Email::normalizeEmailSubject($activity['subject']);
    $replySubject = CRM_Supportcase_Utils_Email::addSubjectPrefix($normalizedSubject, CRM_Supportcase_Utils_Email::REPLY_MODE);
    $forwardSubject = CRM_Supportcase_Utils_Email::addSubjectPrefix($normalizedSubject, CRM_Supportcase_Utils_Email::FORWARD_MODE);
    $fromEmailLabel = !empty($fromEmailsData['emails_data'][0]['label']) ? $fromEmailsData['emails_data'][0]['label'] : '';
    $emailBody = CRM_Supportcase_Utils_Activity::getEmailBody($activity['details']);
    $replyForwardPrefillEmails = $this->getPrefillEmails($ccEmailsData, $toEmailsData, $fromEmailsData, $mainEmailId);
    $replyBody = $this->prepareQuotedBody(
      $activity,
      $fromEmailLabel,
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($ccEmailsData['coma_separated_email_labels']),
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($toEmailsData['coma_separated_email_labels']),
      $normalizedSubject,
      $emailBody,
      CRM_Supportcase_Utils_Email::REPLY_MODE
    );
    $forwardBody = $this->prepareQuotedBody(
      $activity,
      $fromEmailLabel,
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($ccEmailsData['coma_separated_email_labels']),
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($toEmailsData['coma_separated_email_labels']),
      $normalizedSubject,
      $emailBody,
      CRM_Supportcase_Utils_Email::FORWARD_MODE
    );
    $attachments = $this->prepareAttachments($activity);
    $headIcon = $this->getHeadIco($activity);

    return [
      'id' => $activity['id'],
      'view_mode' => [
        'case_id' => $this->params['case_id'],
        'id' => $activity['id'],
        'subject' => $normalizedSubject,
        'head_icon' => $headIcon,
        'email_body' => CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($emailBody['html'])))),
        'date_time' => $activity['activity_date_time'],
        'activity_type' => CRM_Core_PseudoConstant::getName(
          'CRM_Activity_BAO_Activity',
          'activity_type_id',
          $activity['activity_type_id']
        ),
        'author' => $activity['api.ActivityContact.get']['values'][0]['contact_id.display_name'] ?? NULL,
        'attachments' => $attachments,
        'from_contact_email_label' => $fromEmailsData['coma_separated_email_labels'],
        'to_contact_email_label' => $toEmailsData['coma_separated_email_labels'],
        'cc_contact_email_label' => $ccEmailsData['coma_separated_email_labels'],
        'from_contact_data_emails' => $fromEmailsData['emails_data'],
        'to_contact_data_emails' => $toEmailsData['emails_data'],
        'cc_contact_data_emails' => $ccEmailsData['emails_data'],
      ],
      'reply_mode' => [
        'id' => $activity['id'],
        'case_id' => $this->params['case_id'],
        'subject' => $replySubject,
        'email_body' => $replyBody,
        'date_time' => $activity['activity_date_time'],
        'attachments' => [],// attachments always empty for reply mode
        'mode_name' => CRM_Supportcase_Utils_Email::REPLY_MODE,
        'emails' => $replyForwardPrefillEmails,
        'maxFileSize' => $maxFileSize,
        'attachmentsLimit' => $attachmentsLimit,
      ],
      'forward_mode' => [
        'id' => $activity['id'],
        'case_id' => $this->params['case_id'],
        'subject' => $forwardSubject,
        'email_body' => $forwardBody,
        'date_time' => $activity['activity_date_time'],
        'attachments' => $attachments,
        'mode_name' => CRM_Supportcase_Utils_Email::FORWARD_MODE,
        'emails' => $replyForwardPrefillEmails,
        'maxFileSize' => $maxFileSize,
        'attachmentsLimit' => $attachmentsLimit,
      ]
    ];
  }

  /**
   * @param $ccEmailsData
   * @param $toEmailsData
   * @param $fromEmailsData
   * @return array
   */
  private function getPrefillEmails($ccEmailsData, $toEmailsData, $fromEmailsData, $mainEmailId) {
    $toEmailIds = [];

    foreach ($toEmailsData['email_ids'] as $emailId) {
      if ($emailId != $mainEmailId) {
        $toEmailIds[] = $emailId;
      }
    }

    foreach ($fromEmailsData['email_ids'] as $emailId) {
      if ($emailId != $mainEmailId) {
        $toEmailIds[] = $emailId;
      }
    }

    return [
      'cc' => $ccEmailsData['coma_separated_email_ids'],
      'from' => $mainEmailId,
      'to' => implode(',', $toEmailIds),
    ];
  }

  /**
   * @param $activity
   * @param $fromEmailLabel
   * @return string
   */
  private function prepareQuotedBody($activity, $fromEmailLabel, $ccEmailLabels, $toEmailLabels, $subject, $emailBody, $mode) {
    $fromEmailLabel = CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($fromEmailLabel);
    $messageNewLines = "\n\n";
    $date = CRM_Utils_Date::customFormat($activity['activity_date_time']);
    $mailUtilsRenderedTemplate = CRM_Supportcase_Utils_Activity::getRenderedTemplateRelatedToActivity($activity['id']);
    $message = "{$messageNewLines}{$mailUtilsRenderedTemplate}\n\n";
    $addQuotes = TRUE;
    switch ($mode) {
      case CRM_Supportcase_Utils_Email::REPLY_MODE:
        $message .= "On {$date} {$fromEmailLabel} wrote:";
        break;

      case CRM_Supportcase_Utils_Email::FORWARD_MODE:
        $message .= "---------- Forwarded message ---------\n";
        $message .= "From: {$fromEmailLabel}\n";
        $message .= "Date: {$date}\n";
        $message .= "Subject: {$subject}\n";
        $message .= "To: {$toEmailLabels}\n";
        if (!empty($ccEmailLabels)) {
          $message .= "CC: {$ccEmailLabels}\n";
        }
        $addQuotes = FALSE;
        break;
    }

    $quote = trim($emailBody['html']);
    $quote = str_replace("\r", "", $quote);
    if ($addQuotes) {
      $quote = str_replace("\n", "\n> ", $quote);
      $quote = str_replace('> >', '>>', $quote);
      $message = $message . "\n> " . $quote;
    }
    else {
      $message = $message . "\n" . $quote;
    }


    return nl2br($message);
  }

  /**
   * Returns validated params
   *
   * @param $params
   *
   * @return array
   */
  protected function prepareParams($params) {
    try {
      civicrm_api3('Case', 'getsingle', [
        'id' => $params['case_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    return [
      'case_id' => $params['case_id'],
    ];
  }

  /**
   * @param $activity
   * @return array
   */
  private function prepareAttachments($activity) {
    $attachments = [];

    foreach ($activity['api.Attachment.get']['values'] as $attachment) {
      $attachments[] = [
        'file_id' => $attachment['id'],
        'name' => $attachment['name'],
        'icon' => $attachment['icon'],
        'url' => $attachment['url'],
      ];
    }

    return $attachments;
  }

  /**
   * @param $messageParties
   * @return array
   */
  private function prepareEmailsData($messageParties) {
    $emailLabels = [];
    $emailIds = [];
    $emailsData = [];
    foreach ($messageParties as $messageParty) {
      $emailData = CRM_Supportcase_Utils_Email::getEmailContactData($messageParty['email'], $messageParty['contact_id']);
      if (empty($emailData)) {
        continue;
      }

      $icon = '';
      if ($emailData['contact_type'] == 'Individual') {
        $icon = 'com--individual-icon';
      } elseif ($emailData['contact_type'] == 'Organization') {
        $icon = 'com--organization-icon';
      } elseif ($emailData['contact_type'] == 'Household') {
        $icon = 'com--household-icon';
      }

      $emailLabel = CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($emailData['contact_display_name'], $emailData['email']);
      $emailLabels[] = $emailLabel;
      $emailIds[] = $emailData['id'];
      $emailsData[] = [
        'id' => $emailData['id'],
        'label' => $emailLabel,
        'contact_id' => $emailData['contact_id'],
        'contact_type' => $emailData['contact_type'],
        'contact_display_name' => $emailData['contact_display_name'],
        'email' => $emailData['email'],
        'contact_link' => CRM_Utils_System::url('civicrm/contact/view/', [
          'reset' => '1',
          'cid' => $emailData['contact_id'],
        ]),
        'icon' => $icon,
      ];
    }

    return [
      'email_labels' => $emailLabels,
      'coma_separated_email_labels' => implode(', ', $emailLabels),
      'email_ids' => $emailIds,
      'coma_separated_email_ids' => implode(',', $emailIds),
      'emails_data' => $emailsData,
    ];
  }

  /**
   * @param $mainEmailId
   * @param $fromEmailsData
   * @return string
   */
  private function getHeadIco(array $activity) {
    $activityType = CRM_Core_PseudoConstant::getName('CRM_Activity_BAO_Activity', 'activity_type_id', $activity['activity_type_id']);
    return $activityType == 'Email' ? 'fa-reply' : 'fa-inbox';
  }

}
