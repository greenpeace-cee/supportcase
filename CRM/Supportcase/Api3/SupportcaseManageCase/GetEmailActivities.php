<?php

/**
 * Uses on 'SupportcaseManageCase->get_email_activities' api
 */
class CRM_Supportcase_Api3_SupportcaseManageCase_GetEmailActivities extends CRM_Supportcase_Api3_Base {

  /**
   * Modes
   *
   * @var string
   */
  const VIEW = 'view';

  /**
   * Get results of api
   */
  public function getResult() {
    $result = [
      [
        'emails' => [],
        'drafts' => [],
      ]
    ];

    try {
      $activities = civicrm_api3('Activity', 'get', [
        'is_deleted' => '0',
        'case_id' => $this->params['case_id'],
        'activity_type_id' => ['IN' => ['Email', 'Inbound Email']],
        'api.Attachment.get' => [],
        'return' => ['target_contact_id', 'source_contact_id', 'activity_type_id', 'activity_date_time', 'subject', 'details', 'status_id'],
        'api.ActivityContact.get' => ['record_type_id' => 'Activity Assignees', 'return' => 'contact_id.display_name'],
        'options' => ['sort' => 'activity_date_time DESC', 'limit' => 0],
        'status_id' => ['!=' => CRM_Supportcase_Utils_ActivityStatus::SUPPORTCASE_DRAFT_EMAIL],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $result;
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

    $draftActivities = $this->getDraftActivities();

    // if empty draft activities make first not draft activity not collapsed
    if (empty($draftActivities)) {
      foreach ($preparedActivities as $key => $activity) {
        $preparedActivities[$key]['is_collapsed'] = '0';
        break;
      }
    }

    return [
      [
        'emails' => $preparedActivities,
        'drafts' => $draftActivities,
      ]
    ];
  }

  /**
   * @return array
   */
  private function getDraftActivities(): array {
    $preparedDraftActivities = civicrm_api3('SupportcaseDraftEmail', 'get', [
      'case_id' => $this->params['case_id'],
      'return' => [
        'head_icon',
        'subject',
        'activity_id',
        'date_time',
        'case_id',
        'mailutils_message_id',
      ],
    ]);

    if (!empty($preparedDraftActivities['values'])) {
      return $preparedDraftActivities['values'];
    }

    return [];
  }

  /**
   * @param $activity
   * @return array
   */
  private function prepareActivity($activity, $mailUtilsMessage, $mainEmailId) {
    $ccPartyTypeId = CRM_Supportcase_Utils_PartyType::getCcPartyTypeId();
    $toPartyTypeId = CRM_Supportcase_Utils_PartyType::getToPartyTypeId();
    $fromPartyTypeId = CRM_Supportcase_Utils_PartyType::getFromPartyTypeId();
    $toEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessage['id'], $toPartyTypeId);
    $ccEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessage['id'], $ccPartyTypeId);
    $fromEmailsData = CRM_Supportcase_Utils_MailutilsMessageParty::getMailutilsMessagePartiesEmailsData($mailUtilsMessage['id'], $fromPartyTypeId);
    $normalizedSubject = CRM_Supportcase_Utils_Email::normalizeEmailSubject($activity['subject']);
    $emailBody = CRM_Supportcase_Utils_Activity::getEmailBody($activity['details']);
    $attachments = $this->prepareAttachments($activity);
    $headIcon = $this->getHeadIco($activity);

    return [
      'id' => $activity['id'],
      'current_mode' => 'view',
      'is_collapsed' => '1',
      'modes' => [
        'view' => [
          'case_id' => $this->params['case_id'],
          'id' => $activity['id'],
          'subject' => $normalizedSubject,
          'head_icon' => $headIcon,
          'email_body' => CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($emailBody['html'])))),
          'date_time' => $activity['activity_date_time'],
          'activity_type' => CRM_Core_PseudoConstant::getName('CRM_Activity_BAO_Activity', 'activity_type_id', $activity['activity_type_id']),
          'author' => $activity['api.ActivityContact.get']['values'][0]['contact_id.display_name'] ?? NULL,
          'attachments' => $attachments,
          'from_contact_email_label' => $fromEmailsData['coma_separated_email_labels'],
          'to_contact_email_label' => $toEmailsData['coma_separated_email_labels'],
          'cc_contact_email_label' => $ccEmailsData['coma_separated_email_labels'],
          'from_contact_data_emails' => $fromEmailsData['emails_data'],
          'to_contact_data_emails' => $toEmailsData['emails_data'],
          'cc_contact_data_emails' => $ccEmailsData['emails_data'],
        ]
      ],
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
    try {
      $case = civicrm_api3('Case', 'getsingle', [
        'id' => $params['case_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      throw new api_Exception('Case does not exist.', 'case_does_not_exist');
    }

    $categoryFieldName = CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Supportcase_Install_Entity_CustomField::CATEGORY, CRM_Supportcase_Install_Entity_CustomGroup::CASE_DETAILS, TRUE);

    return [
      'case' => $case,
      'case_id' => $params['case_id'],
      'case_category_id' => (!empty($case[$categoryFieldName])) ? $case[$categoryFieldName] : NULL,
    ];
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

  /**
   * @param array $activity
   * @return string
   */
  private function getHeadIco(array $activity): string {
    $activityType = CRM_Core_PseudoConstant::getName('CRM_Activity_BAO_Activity', 'activity_type_id', $activity['activity_type_id']);
    return $activityType == 'Email' ? 'fa-reply' : 'fa-inbox';
  }

}
