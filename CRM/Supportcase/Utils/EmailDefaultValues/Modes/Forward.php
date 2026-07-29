<?php

class CRM_Supportcase_Utils_EmailDefaultValues_Modes_Forward extends CRM_Supportcase_Utils_EmailDefaultValues_Modes_Base {

  public function getValues(): array {
    $defaultValues = $this->getDefaultFields();

    $fromActivity = $this->getFromActivity();
    $attachments = $this->prepareAttachments($fromActivity);
    $case = $this->getCase();
    $mailUtilsMessage = $this->getRelatedMailUtilsMessage();
    $mainEmailId = $this->getMainEmailId((int) $mailUtilsMessage['mail_setting_id']);
    $emailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getEmailsData($mailUtilsMessage['id']);
    $normalizedSubject = CRM_Supportcase_Utils_Email::normalizeEmailSubject($fromActivity['subject']);
    $subject = CRM_Supportcase_Utils_Email::addSubjectPrefix($normalizedSubject, CRM_Supportcase_Utils_Email::FORWARD_MODE);
    $fromEmailLabel = !empty($emailsData['from']['emails_data'][0]['label']) ? $emailsData['from']['emails_data'][0]['label'] : '';
    $emailBody = CRM_Supportcase_Utils_Activity::getEmailBody($fromActivity['details']);
    $prefillEmails = $this->getPrefillEmails($emailsData['cc'], $emailsData['to'], $emailsData['from'], $mainEmailId);
    $preparedEmailBody = $this->prepareQuotedBody(
      $fromActivity,
      $fromEmailLabel,
      CRM_Supportcase_Utils_Case::getFirstClient($case),
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($emailsData['cc']['coma_separated_email_labels']),
      CRM_Supportcase_Utils_EmailSearch::replaceHtmlSymbolInEmailLabel($emailsData['to']['coma_separated_email_labels']),
      $normalizedSubject,
      $emailBody,
      CRM_Supportcase_Utils_Email::FORWARD_MODE
    );

    $defaultValues['attachments'] = $attachments;
    $defaultValues['subject'] = $subject;
    $defaultValues['body'] = json_encode(['html' => $preparedEmailBody, 'text' => CRM_Utils_String::htmlToText($preparedEmailBody)]);
    $defaultValues['fromEmails'] = CRM_Supportcase_Utils_EmailSearch::searchByCommaSeparatedIds($prefillEmails['from']);
    $defaultValues['case_category_id'] = $case['case_category_id'];
    $defaultValues['token_contact_id'] = CRM_Supportcase_Utils_Case::getFirstClient($case);


    return $defaultValues;
  }

  protected function getFromActivityParams(): array {
    $activityParams = parent::getFromActivityParams();
    $activityParams['api.Attachment.get'] = [];

    return $activityParams;
  }

  /**
   * @param $activity
   * @return array
   */
  private static function prepareAttachments($activity): array {
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

}
