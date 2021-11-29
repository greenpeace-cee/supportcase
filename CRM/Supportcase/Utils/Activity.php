<?php

class CRM_Supportcase_Utils_Activity {

  /**
   * Gets list of related contacts to activity
   * (1 assignee, 2 creator, 3 focus or target)
   *
   * @param $activityId
   * @return array
   */
  public static function getRelatedContacts($activityId) {
    $assignee = [];
    $creator = [];
    $target = [];

    if (!empty($activityId)) {
      try {
        $activityContacts = civicrm_api3('ActivityContact', 'get', [
          'sequential' => 1,
          'return' => ["contact_id.display_name", "activity_id", "contact_id.id", "record_type_id"],
          'activity_id' => $activityId,
          'options' => ['limit' => 0],
        ]);
      } catch (CiviCRM_API3_Exception $e) {}

      if (!empty($activityContacts['values'])) {
        foreach ($activityContacts['values'] as $activityContact) {
          $contact = [
            'display_name' => $activityContact['contact_id.display_name'],
            'link' => CRM_Utils_System::url('civicrm/contact/view/', [
              'reset' => '1',
              'cid' => $activityContact['contact_id.id'],
            ])
          ];

          if ($activityContact['record_type_id'] == '1') {
            $assignee[] = $contact;
          } else if ($activityContact['record_type_id'] == '2') {
            $creator[] = $contact;
          } else if ($activityContact['record_type_id'] == '3') {
            $target[] = $contact;
          }
        }
      }
    }

    return [
      'assignee' => $assignee,
      'creator' => $creator,
      'target' => $target,
    ];
  }

  /**
   * @param $activityId
   * @return array|null
   */
  public static function getRelatedMailUtilsMessage($activityId) {
    if (empty($activityId)) {
      return null;
    }

    $mailutilsMessages = \Civi\Api4\MailutilsMessage::get()
      ->addSelect('*')
      ->addWhere('activity_id', '=', $activityId)
      ->setLimit(1)
      ->execute();
    foreach ($mailutilsMessages as $item) {
      return $item;
    }

    return null;
  }

  /**
   * This method copies attachment from one activity to another activity
   * It doesn't copy file, only add connection to the same file
   *
   * @param $fromActivityId
   * @param $toActivityId
   * @param $copyOnlyFileIds
   */
  public static function copyAttachment($fromActivityId, $toActivityId, $copyOnlyFileIds = null) {
    $isNeedToCopyAllFiles = is_null($copyOnlyFileIds);

    if (empty($fromActivityId) || empty($toActivityId)) {
      return;
    }

    $entityTable = 'civicrm_activity';
    $result = CRM_Core_DAO::executeQuery('SELECT file_id FROM civicrm_entity_file WHERE entity_table = %1 AND entity_id = %2', [
      1 => [$entityTable , 'String'],
      2 => [$fromActivityId , 'Integer'],
    ]);

    $fileIds = [];
    while ($result->fetch()) {
      $fileIds[] = $result->file_id;
    }

    if (empty($fileIds)) {
      return;
    }

    foreach ($fileIds as $fileId) {
      $isNeedToCopy = false;
      if ($isNeedToCopyAllFiles) {
        $isNeedToCopy = true;
      }  else if (is_array($copyOnlyFileIds)) {
        $isNeedToCopy = in_array($fileId, $copyOnlyFileIds);
      }

      if ($isNeedToCopy && !CRM_Supportcase_Utils_EntityFile::isEntityFileExist($entityTable, $toActivityId, $fileId)) {
        CRM_Supportcase_Utils_EntityFile::createEntityFile($entityTable, $toActivityId, $fileId);
      }
    }
  }

  /**
   * Get main email from settings related to this activity
   *
   * @param $activityId
   * @return string
   */
  public static function getMainEmail($activityId) {
    $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($activityId);

    if (empty($mailUtilsMessage)) {
      return '';
    }

    $mailUtilsSettings = CRM_Supportcase_Utils_MailutilsMessage::getRelatedMailUtilsSetting($mailUtilsMessage['mail_setting_id']);

    if (empty($mailUtilsSettings['smtp_username'])) {
      return '';
    }

    return $mailUtilsSettings['smtp_username'];
  }

  /**
   * @param $activityId
   * @return array
   */
  public static function getTemplateMessageRelatedToActivity($activityId) {
    $mailUtilsMessage = CRM_Supportcase_Utils_Activity::getRelatedMailUtilsMessage($activityId);

    if (empty($mailUtilsMessage)) {
      return [];
    }

    $mailUtilsSetting = CRM_Supportcase_Utils_MailutilsMessage::getRelatedMailUtilsSetting($mailUtilsMessage['mail_setting_id']);
    if (empty($mailUtilsSetting)) {
      return [];
    }

    if (empty($mailUtilsSetting['mailutils_template_id'])) {
      return [];
    }

    $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::get()
      ->addWhere('id', '=', $mailUtilsSetting['mailutils_template_id'])
      ->setLimit(1)
      ->execute()
      ->first();


    if (empty($mailutilsTemplate)) {
      return [];
    }

    return $mailutilsTemplate;
  }

  /**
   * @param $activityId
   * @return string
   */
  public static function getRenderedTemplateRelatedToActivity($activityId) {
    $mailutilsTemplate = self::getTemplateMessageRelatedToActivity($activityId);

    if (empty($mailutilsTemplate)) {
      return '';
    }

    return $mailutilsTemplate['message'];
  }

}
