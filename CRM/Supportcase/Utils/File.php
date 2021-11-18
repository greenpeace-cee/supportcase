<?php

class CRM_Supportcase_Utils_File {

  /**
   * @param $entityTable
   * @param $entityId
   * @return array
   */
  public static function getFileData($entityTable, $entityId) {
    if (empty($entityTable) || empty($entityId)) {
      return [];
    }

    $result = CRM_Core_DAO::executeQuery('
        SELECT 
            civicrm_entity_file.file_id as file_id,
            civicrm_entity_file.entity_id as entity_id,
            civicrm_entity_file.entity_table as entity_table,
            civicrm_file.uri as file_uri
        FROM civicrm_entity_file 
        LEFT JOIN civicrm_file ON civicrm_file.id = civicrm_entity_file.file_id
        WHERE entity_table = %1 AND entity_id = %2
      ', [
      1 => [$entityTable , 'String'],
      2 => [$entityId , 'Integer'],
    ]);

    while ($result->fetch()) {
      return [
        'file_id' => $result->file_id,
        'entity_id' => $result->entity_id,
        'entity_table' => $result->entity_table,
        'file_uri' => $result->file_uri,
      ];
    }

    return [];
  }

}
