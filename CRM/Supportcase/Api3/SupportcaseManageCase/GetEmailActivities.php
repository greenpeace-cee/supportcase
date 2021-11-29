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
        try {
          $preparedActivities[] = $this->prepareActivity($activity, $mailUtilsMessage);
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
  private function prepareActivity($activity, $mailUtilsMessage) {
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
    $fromContactDisplayName = !empty($fromEmailsData[0]['contact_display_name']) ? $fromEmailsData[0]['contact_display_name'] : '';
    $replyForwardBody = $this->prepareReplyBody($activity, $fromContactDisplayName);
    $attachments = $this->prepareAttachments($activity);
    $replyForwardPrefillEmails = $this->getPrefillEmails($activity['id'], $ccEmailsData, $toEmailsData, $fromEmailsData);

    return [
      'id' => $activity['id'],
      'view_mode' => [
        'case_id' => $this->params['case_id'],
        'id' => $activity['id'],
        'subject' => $normalizedSubject,
        'email_body' => CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($activity['details'])))),
        'date_time' => $activity['activity_date_time'],
        'attachments' => $attachments,
        'from_contact_email_label' => $fromEmailsData['coma_separated_email_labels'],
        'to_contact_email_label' => $toEmailsData['coma_separated_email_labels'],
        'cc_contact_email_label' => $ccEmailsData['coma_separated_email_labels'],
      ],
      'reply_mode' => [
        'id' => $activity['id'],
        'case_id' => $this->params['case_id'],
        'subject' => $replySubject,
        'email_body' => $replyForwardBody,
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
        'email_body' => $replyForwardBody,
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
   * @param $activityId
   * @param $ccEmailsData
   * @param $toEmailsData
   * @param $fromEmailsData
   * @return array
   */
  private function getPrefillEmails($activityId, $ccEmailsData, $toEmailsData, $fromEmailsData) {
    $mainEmailId =  CRM_Supportcase_Utils_Activity::getMainEmailId($activityId);
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
   * @param $fromContactDisplayName
   * @return string
   */
  private function prepareReplyBody($activity, $fromContactDisplayName) {
    $messageNewLines = "\n \n \n";
    $date = CRM_Utils_Date::customFormat($activity['activity_date_time']);
    $mailUtilsRenderedTemplate = CRM_Supportcase_Utils_Activity::getRenderedTemplateRelatedToActivity($activity['id']);
    $message = "{$messageNewLines}{$mailUtilsRenderedTemplate}\n\nOn {$date} {$fromContactDisplayName} wrote:";
    $quote = trim(CRM_Utils_String::stripAlternatives($activity['details']));
    $quote = str_replace("\r", "", $quote);
    $quote = str_replace("\n", "\n> ", $quote);
    $quote = str_replace('> >', '>>', $quote);
    $message = $message . "\n> " . $quote;

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
      'case_id' => $params['case_id']
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
      $emailLabel = CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($emailData['contact_display_name'], $emailData['email']);
      $emailLabels[] = $emailLabel;
      $emailIds[] = $emailData['id'];
      $emailsData[] = [
        'id' => $emailData['id'],
        'label' => $emailLabel,
        'contact_id' => $emailData['contact_id'],
        'contact_display_name' => $emailData['contact_display_name'],
        'email' => $emailData['email'],
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

}
