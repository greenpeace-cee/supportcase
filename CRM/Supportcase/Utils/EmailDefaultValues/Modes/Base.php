<?php

abstract class CRM_Supportcase_Utils_EmailDefaultValues_Modes_Base {

  /**
   * Mode
   *
   * @var string
   */
  protected $mode;

  /**
   * Case id
   *
   * @var int
   */
  protected $caseId;

  /**
   * From activity id
   *
   * @var int|null
   */
  protected $fromActivityId;

  /**
   * @param string $mode
   * @param int|string $caseId
   * @param int|string $fromActivityId
   */
  public function __construct(string $mode, $caseId, $fromActivityId = null) {
    $this->mode = $mode;
    $this->caseId = $caseId;
    $this->fromActivityId = $fromActivityId;
  }

  abstract public function getValues();

  /**
   * @return array
   */
  protected function getDefaultFields(): array {
    return [
      'toEmails' => '',
      'fromEmails' => '',
      'ccEmails' => '',
      'mode' => $this->mode,
      'body' => json_encode(['html' => '', 'text' => '']),
      'subject' => '',
      'forwardFileIds' => null,
      'attachments' => [],
    ];
  }

  protected function getFromActivityParams() {
    return [
      'id' => $this->fromActivityId,
      'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details', 'status_id'],
      'api.ActivityContact.get' => ['record_type_id' => 'Activity Assignees', 'return' => 'contact_id.display_name'],
    ];
  }

  /**
   * @return array
   */
  protected function getFromActivity(): array {
    try {
      $activity = civicrm_api3('Activity', 'getsingle', $this->getFromActivityParams());
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Error. Cannot get Activity', 'error_getting_activity');
    }

    return $activity;
  }

  /**
   * @return array
   */
  protected function getCase(): array {
    try {
      $case = civicrm_api3('Case', 'getsingle', [
        'id' => $this->caseId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);
    $case['case_category_id'] = (!empty($case[$categoryFieldName])) ? $case[$categoryFieldName] : NULL;

    return $case;
  }

  /**
   * @return array
   */
  protected function getRelatedMailUtilsMessage(): array {
    $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($this->fromActivityId);
    if (empty($mailUtilsMessage)) {
      throw new api_Exception('Error. Cannot get MailUtilsMessage for activity id=' . $this->fromActivityId, 'error_getting_mailutils_message');
    }

    return $mailUtilsMessage;
  }

  /**
   * @param $mailSettingId
   * @return int
   */
  protected function getMainEmailId($mailSettingId): int {
    $mainEmailId =  CRM_Supportcase_Utils_Activity::getMainEmailId($mailSettingId);
    if (empty($mainEmailId)) {
      throw new api_Exception('Error. Cannot get main email for mailSettingId = ' . $mailSettingId, 'error_getting_main_email');
    }

    return (int) $mainEmailId;
  }

  /**
   * @param $ccEmailsData
   * @param $toEmailsData
   * @param $fromEmailsData
   * @param $mainEmailId
   *
   * @return array
   */
  protected function getPrefillEmails($ccEmailsData, $toEmailsData, $fromEmailsData, $mainEmailId): array {
    $toEmailIds = array_unique(array_merge(
      $this->getFilteredEmailIds($toEmailsData['emails_data'], $mainEmailId),
      $this->getFilteredEmailIds($fromEmailsData['emails_data'], $mainEmailId)
    ));
    $ccEmailIds = $this->getFilteredEmailIds($ccEmailsData['emails_data'], $mainEmailId);

    return [
      'cc' => implode(',', $ccEmailIds),
      'from' => $mainEmailId,
      'to' => implode(',', $toEmailIds),
    ];
  }

  /**
   * Return emails that are not $mainEmailId and not on the discard list
   *
   * @param array $emailData
   * @param $mainEmailId
   *
   * @return array
   */
  protected function getFilteredEmailIds(array $emailData, $mainEmailId): array {
    $emailIds = [];
    $discardEmails = \Civi::settings()->get('supportcase_discard_mail_aliases');
    foreach ($emailData as $email) {
      if (in_array($email['email'], $discardEmails) || $email['id'] == $mainEmailId) {
        continue;
      }
      $emailIds[] = $email['id'];
    }
    return $emailIds;
  }

  /**
   * @param $ccEmailsData
   * @param $toEmailsData
   * @param $fromEmailsData
   * @param $mainEmailId
   *
   * @return array
   */
  protected function getReplyPrefillEmails($ccEmailsData, $toEmailsData, $fromEmailsData, $mainEmailId): array {
    // prefill To: with the From: address unless it's an outbound email (i.e. matches $mainEmailId)
    $to = $this->getFilteredEmailIds($fromEmailsData['emails_data'], $mainEmailId);
    if (empty($to)) {
      // this is a reply to an outbound email, prefill from To:
      $to = $this->getFilteredEmailIds($toEmailsData['emails_data'], $mainEmailId);
    }
    return [
      'cc' => '',// for reply cc is empty
      'from' => $mainEmailId,
      'to' => $to[0], // use first match
    ];
  }

  /**
   * @param $activity
   * @param $fromEmailLabel
   * @return string
   */
  protected function prepareQuotedBody($activity, $fromEmailLabel, $fromEmailContactId, $ccEmailLabels, $toEmailLabels, $subject, $emailBody, $mode) {
    $fromEmailLabel = CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($fromEmailLabel);
    $date = CRM_Utils_Date::customFormat($activity['activity_date_time']);
    $mailUtilsRenderedTemplate = CRM_Supportcase_Utils_Activity::getRenderedTemplateRelatedToActivity($activity['id']);
    $mailUtilsRenderedTemplate = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($mailUtilsRenderedTemplate, $fromEmailContactId);
    $message = "{$mailUtilsRenderedTemplate}\n\n";
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
   * @param $caseCategoryId
   * @return false|array
   */
  protected function getFirstRelatedMailutilsSetting($caseCategoryId) {
    if (empty($caseCategoryId)) {
      return false;
    }

    $mailutilsSetting = \Civi\Api4\MailutilsSetting::get(FALSE)
      ->addSelect('*')
      ->addWhere('support_case_category_id', '=', $caseCategoryId)
      ->addOrderBy('id', 'ASC')
      ->setLimit(1)
      ->execute()
      ->first();

    if (empty($mailutilsSetting)) {
      return false;
    }

    return $mailutilsSetting;
  }

}
