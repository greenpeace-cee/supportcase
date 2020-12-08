<?php

class CRM_Supportcase_Utils_CaseManager {

  /**
   * Get case manger contact ids
   * Based on CiviCRM method: CRM_Case_BAO_Case::getCaseManagerContact
   *
   * @param int $caseType
   * @param int $caseId
   *
   * @return array
   */
  public static function getCaseManagerContactIds($caseType, $caseId) {
    if (!$caseType || !$caseId) {
      return [];
    }

    $managersIds = [];
    $relationshipTypeInfo = CRM_Supportcase_Utils_Setting::getCaseManagerRelationshipTypeInfo($caseType);

    if (!empty($relationshipTypeInfo)) {
      $managerRoleQuery = "
        SELECT civicrm_contact.id as casemanager_id, civicrm_contact.sort_name as casemanager
        FROM civicrm_contact
        LEFT JOIN civicrm_relationship ON (civicrm_relationship." . $relationshipTypeInfo['manager_column_name'] . " = civicrm_contact.id
        AND civicrm_relationship.relationship_type_id = %1) AND civicrm_relationship.is_active
        LEFT JOIN civicrm_case ON civicrm_case.id = civicrm_relationship.case_id
        WHERE civicrm_case.id = %2 AND is_active = 1";

      $dao = CRM_Core_DAO::executeQuery($managerRoleQuery, [
        1 => [$relationshipTypeInfo['manager_relationship_type_id'], 'Integer'],
        2 => [$caseId, 'Integer'],
      ]);

      while ($dao->fetch()) {
        $managersIds[] = (int) $dao->casemanager_id;
      }
    }

    return $managersIds;
  }

}
