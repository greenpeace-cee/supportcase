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
      $activities = [];
    }

    $preparedActivities = [];
    if (!empty($activities['values'])) {
      foreach ($activities['values'] as $activity) {
        try {
          $preparedActivities[] = $this->prepareActivity($activity);
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
  private function prepareActivity($activity) {
    $fromContact = civicrm_api3('Contact', 'getsingle', [
      'id' => $activity['source_contact_id'],
      'return' => ['id', 'email', 'display_name', 'email_id'],
    ]);

    $toContacts = [];
    $toContactsEmailIds = [];
    $toContactsEmailLabels = [];
    foreach ($activity['target_contact_id'] as $contactId) {
      try {
        $toContact = civicrm_api3('Contact', 'getsingle', [
          'id' => $contactId,
          'return' => ['id', 'email', 'display_name', 'email_id'],
        ]);

        $toContactsEmailIds[] = $toContact['id'];
        $toContactsEmailLabels[] = CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($toContact['display_name'], $toContact['email']);
        $toContacts[] = [
          'id' => $toContact['id'],
          'email_id' => $toContact['email_id'],
          'display_name' => $toContact['display_name'],
          'email' => $toContact['email'],
          'email_label' => CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($toContact['display_name'], $toContact['email']),
        ];
      } catch (CiviCRM_API3_Exception $e) {}
    }

    $attachmentsLimit = CRM_Supportcase_Utils_Setting::getActivityAttachmentLimit();
    $maxFileSize = CRM_Supportcase_Utils_Setting::getMaxFilesSize();
    $normalizedSubject = CRM_Supportcase_Utils_Email::normalizeEmailSubject($activity['subject']);
    $replySubject = CRM_Supportcase_Utils_Email::addSubjectPrefix($normalizedSubject, CRM_Supportcase_Utils_Email::REPLY_MODE);
    $forwardSubject = CRM_Supportcase_Utils_Email::addSubjectPrefix($normalizedSubject, CRM_Supportcase_Utils_Email::FORWARD_MODE);
    $replyForwardBody = $this->prepareReplyBody($activity, $fromContact);
    $attachments = [];

    foreach ($activity['api.Attachment.get']['values'] as $attachment) {
      $attachments[] = [
        'file_id' => $attachment['id'],
        'name' => $attachment['name'],
        'icon' => $attachment['icon'],
        'url' => $attachment['url'],
      ];
    }

    return [
      'id' => $activity['id'],
      'view_mode' => [
        'case_id' => $this->params['case_id'],
        'id' => $activity['id'],
        'subject' => $normalizedSubject,
        'email_body' => CRM_Utils_String::purifyHTML(nl2br(trim(CRM_Utils_String::stripAlternatives($activity['details'])))),
        'date_time' => $activity['activity_date_time'],
        'attachments' => $attachments,
        'from_contact' => [
          'id' => $fromContact['id'],
          'email_id' => $fromContact['email_id'],
          'display_name' => $fromContact['display_name'],
          'email' => $fromContact['email'],
          'email_label' => CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($fromContact['display_name'], $fromContact['email']),
        ],
        'to_contacts' => $toContacts,
        'to_contacts_email_label' => implode(', ', $toContactsEmailLabels),
      ],
      'reply_mode' => [
        'id' => $activity['id'],
        'case_id' => $this->params['case_id'],
        'subject' => $replySubject,
        'email_body' => $replyForwardBody,
        'date_time' => $activity['activity_date_time'],
        'attachments' => [],// attachments always empty for reply mode
        'mode_name' => CRM_Supportcase_Utils_Email::REPLY_MODE,
        'emails' => [// here are switched 'to' and 'from' contact at reply mode, it is correct
          'cc' => '',
          'from' => implode(',', $toContactsEmailIds),
          'to' => $fromContact['email_id'],
        ],
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
        'emails' => [// here are switched 'to' and 'from' contact at forward mode, it is correct
          'cc' => '',
          'from' => implode(',', $toContactsEmailIds),
          'to' => $fromContact['email_id'],
        ],
        'maxFileSize' => $maxFileSize,
        'attachmentsLimit' => $attachmentsLimit,
      ]
    ];
  }

  /**
   * @param $activity
   * @param $fromContact
   * @return string
   */
  private function prepareReplyBody($activity, $fromContact) {
    $messageNewLines = "\n \n \n";
    $date = CRM_Utils_Date::customFormat($activity['activity_date_time']);
    $mailUtilsRenderedTemplate = $this->getTemplateRelatedToActivity($activity['id']);//TODO: Check if we need render template or only put key of template
    $message = "{$messageNewLines}{$mailUtilsRenderedTemplate}\n\nOn {$date} {$fromContact['display_name']} wrote:";
    $quote = trim(CRM_Utils_String::stripAlternatives($activity['details']));
    $quote = str_replace("\r", "", $quote);
    $quote = str_replace("\n", "\n> ", $quote);
    $quote = str_replace('> >', '>>', $quote);
    $message = $message . "\n> " . $quote;

    return nl2br($message);
  }

  /**
   * @param $activityId
   * @return string
   */
  private function getTemplateRelatedToActivity($activityId) {
    $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($activityId);

    if (empty($mailUtilsMessage)) {
      return '';
    }

    $mailUtilsSetting = CRM_Supportcase_Utils_MailutilsMessage::getRelatedMailUtilsSetting($mailUtilsMessage['mail_setting_id']);
    if (empty($mailUtilsSetting)) {
      return '';
    }

    if (empty($mailUtilsSetting['mailutils_template_id'])) {
      return '';
    }

    $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::get()
      ->addWhere('id', '=', $mailUtilsSetting['mailutils_template_id'])
      ->setLimit(1)
      ->execute()
      ->first();


    if (empty($mailutilsTemplate)) {
      return '';
    }

    return $mailutilsTemplate['message'];
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

}
