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
    try {
      $toContact = civicrm_api3('Contact', 'getsingle', [
        'id' => $activity['target_contact_id'][0],
        'return' => ['id', 'email', 'display_name', 'email_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $toContact = [
        'id' => '',
        'display_name' => '',
        'email' => '',
        'email_id' => '',
      ];
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
        'to_contact' => [
          'id' => $toContact['id'],
          'email_id' => $toContact['email_id'],
          'display_name' => $toContact['display_name'],
          'email' => $toContact['email'],
          'email_label' => CRM_Supportcase_Utils_EmailSearch::prepareEmailLabel($toContact['display_name'], $toContact['email']),
        ],
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
          'from' => $toContact['email_id'],
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
          'from' => $toContact['email_id'],
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
    $signature = $this->generateSignature();
    $message = "{$messageNewLines}{$signature}\n\nOn {$date} {$fromContact['display_name']} wrote:";
    $quote = trim(CRM_Utils_String::stripAlternatives($activity['details']));
    $quote = str_replace("\r", "", $quote);
    $quote = str_replace("\n", "\n> ", $quote);
    $quote = str_replace('> >', '>>', $quote);
    $message = $message . "\n> " . $quote;

    return nl2br($message);
  }

  /**
   * @return string
   */
  private function generateSignature() {
    $signature = "\n Mit freundlichen Grüßen";
    $signature .= "<hr>";
    $signature .= "\n Greenpeace in Zentral- und Osteuropa";
    $signature .= "\n Wiedner Hauptstraße 120-124, 1050 Wien";
    $signature .= "\n Telefon: +43 (0)1 545 45 80";
    $signature .= "\n Spendenkonto: IBAN AT24 20111 82221219800";

    return $signature;
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
