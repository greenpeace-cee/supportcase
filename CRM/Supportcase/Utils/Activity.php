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
      ->addSelect('id')
      ->addSelect('mailutils_thread_id')
      ->addSelect('mail_setting_id')
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

      if ($isNeedToCopy && !self::isEntityFileExist($entityTable, $toActivityId, $fileId)) {
        self::createEntityFile($entityTable, $toActivityId, $fileId);
      }
    }
  }

  /**
   * @param $entityTable
   * @param $toActivityId
   * @param $fileId
   * @return bool
   */
  private static function isEntityFileExist($entityTable, $toActivityId, $fileId) {
    $result = CRM_Core_DAO::executeQuery('
        SELECT id 
        FROM civicrm_entity_file 
        WHERE entity_table = %1 AND entity_id = %2 AND file_id = %3 
      ', [
      1 => [$entityTable , 'String'],
      2 => [$toActivityId , 'Integer'],
      3 => [$fileId , 'Integer'],
    ]);

    while ($result->fetch()) {
      return true;
    }

    return false;
  }

  /**
   * @param $entityTable
   * @param $toActivityId
   * @param $fileId
   */
  private static function createEntityFile($entityTable, $toActivityId, $fileId) {
    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_entity_file(entity_table, entity_id, file_id) VALUES (%1, %2, %3)', [
      1 => [$entityTable , 'String'],
      2 => [$toActivityId , 'Integer'],
      3 => [$fileId , 'Integer'],
    ]);
  }

}
