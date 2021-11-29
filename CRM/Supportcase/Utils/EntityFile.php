<?php

class CRM_Supportcase_Utils_EntityFile {

  /**
   * @param $entityTable
   * @param $toActivityId
   * @param $fileId
   * @return bool
   */
  public static function isEntityFileExist($entityTable, $toActivityId, $fileId) {
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
  public static function createEntityFile($entityTable, $toActivityId, $fileId) {
    CRM_Core_DAO::executeQuery('INSERT INTO civicrm_entity_file(entity_table, entity_id, file_id) VALUES (%1, %2, %3)', [
      1 => [$entityTable , 'String'],
      2 => [$toActivityId , 'Integer'],
      3 => [$fileId , 'Integer'],
    ]);
  }

}
