<?php

abstract class CRM_Supportcase_Api3_SupportcaseDraftEmail_Get_Base extends CRM_Supportcase_Api3_Base {

  /**
   * @param $activity
   * @param $mailUtilsMessageId
   * @return array
   */
  protected function prepareDraftActivity($activity, $mailUtilsMessageId): array {
    $data = [
      'activity_id' => $activity['id'],
      'mailutils_message_id' => $mailUtilsMessageId,
    ];

    if (in_array('email_auto_save_interval_time', $this->params['returnFields'])) {
      $data['email_auto_save_interval_time'] = CRM_Supportcase_Utils_Setting::getEmailAutoSaveIntervalTime();
    }

    if (in_array('subject', $this->params['returnFields'])) {
      $data['subject'] = CRM_Supportcase_Utils_Email::normalizeEmailSubject($activity['subject']);;
    }

    if (in_array('max_file_size', $this->params['returnFields'])) {
      $data['max_file_size'] = CRM_Supportcase_Utils_Setting::getMaxFilesSize();
    }

    if (in_array('attachments_limit', $this->params['returnFields'])) {
      $data['attachments_limit'] = CRM_Supportcase_Utils_Setting::getActivityAttachmentLimit();
    }

    if (in_array('cc_email_ids', $this->params['returnFields'])) {
      $ccPartyTypeId = CRM_Supportcase_Utils_PartyType::getCcPartyTypeId();
      $ccEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $ccPartyTypeId);
      $data['cc_email_ids'] = $ccEmailsData['coma_separated_email_ids'];
    }

    if (in_array('from_email_ids', $this->params['returnFields'])) {
      $fromPartyTypeId = CRM_Supportcase_Utils_PartyType::getFromPartyTypeId();
      $fromEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $fromPartyTypeId);
      $data['from_email_ids'] = $fromEmailsData['coma_separated_email_ids'];
    }

    if (in_array('to_email_ids', $this->params['returnFields'])) {
      $toPartyTypeId = CRM_Supportcase_Utils_PartyType::getToPartyTypeId();
      $toEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessageId, $toPartyTypeId);
      $data['to_email_ids'] = $toEmailsData['coma_separated_email_ids'];
    }

    if (in_array('email_body', $this->params['returnFields'])) {
      $emailBody = CRM_Supportcase_Utils_Activity::getEmailBody($activity['details']);
      $data['email_body'] = CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($emailBody['html']))));
    }

    if (in_array('attachments', $this->params['returnFields'])) {
      $attachments = $this->prepareAttachments($activity);
      $data['attachments'] = $attachments;
    }

    if (in_array('is_collapsed', $this->params['returnFields'])) {
      $data['is_collapsed'] = '0';
    }

    if (in_array('head_icon', $this->params['returnFields'])) {
      $data['head_icon'] = 'fa-file';
    }

    if (in_array('case_id', $this->params['returnFields'])) {
      $data['case_id'] = $this->params['case_id'];
    }

    if (in_array('date_time', $this->params['returnFields'])) {
      $data['date_time'] = $activity['activity_date_time'];
    }

    if (in_array('activity_type', $this->params['returnFields'])) {
      $data['activity_type'] = CRM_Core_PseudoConstant::getName('CRM_Activity_BAO_Activity', 'activity_type_id', $activity['activity_type_id']);
    }

    if (in_array('case_category_id', $this->params['returnFields'])) {
      $data['case_category_id'] = $this->params['case_category_id'];
    }

    if (in_array('token_contact_id', $this->params['returnFields'])) {
      $data['token_contact_id'] = CRM_Supportcase_Utils_Case::getFirstClient($this->params['case']);
    }

    return $data;
  }

  /**
   * @param $activity
   * @return array
   */
  private function prepareAttachments($activity): array {
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

  protected function getCaseCategoryId($caseId) {
    try {
      $case = civicrm_api3('Case', 'getsingle', [
        'id' => $caseId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);

    return (!empty($case[$categoryFieldName])) ? $case[$categoryFieldName] : NULL;
  }

}
