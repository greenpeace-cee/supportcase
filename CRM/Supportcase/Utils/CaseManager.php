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

  /**
   * Set manager to case
   *
   * @param $managerContactId
   * @param $clientContactId
   * @param $caseId
   */
  public static function setManager($managerContactId, $clientContactId, $caseId) {
    if (empty($managerContactId) || empty($clientContactId) || empty($caseId)) {
      return;
    }

    $relationship = self::findRelationship($managerContactId, $clientContactId, $caseId);

    $relationshipParams = [];
    if (empty($relationship)) {
      $relationshipTypeInfo = CRM_Supportcase_Utils_Setting::getCaseManagerRelationshipTypeInfo(CRM_Supportcase_Install_Entity_CaseType::SUPPORT_CASE);
      $relationshipParams = [
        'relationship_type_id' => $relationshipTypeInfo['manager_relationship_type_id'],
        'case_id' => $caseId,
        $relationshipTypeInfo['manager_column_name'] => $managerContactId,
        $relationshipTypeInfo['client_column_name'] => $clientContactId,
      ];
    } elseif($relationship['is_active'] == 0) {
      $relationshipParams['id'] = $relationship['id'];
      $relationshipParams['is_active'] = 1;
    }

    if (empty(!$relationshipParams)) {
      civicrm_api3('Relationship', 'create', $relationshipParams);
    }
  }

  /**
   * Unset manager to case
   *
   * @param $managerContactId
   * @param $clientContactId
   * @param $caseId
   */
  public static function unsetManager($managerContactId, $clientContactId, $caseId) {
    if (empty($managerContactId) || empty($clientContactId) || empty($caseId)) {
      return;
    }

    $relationship = self::findRelationship($managerContactId, $clientContactId, $caseId);
    if (!empty($relationship) && $relationship['is_active'] == 1) {
      civicrm_api3('Relationship', 'create', [
        'id' => $relationship['id'],
        'is_active' => 0
      ]);
    }
  }

  /**
   * Finds relationship
   *
   * @param $managerContactId
   * @param $clientContactId
   * @param $caseId
   * @return array
   */
  private static function findRelationship($managerContactId, $clientContactId, $caseId) {
    $relationshipTypeInfo = CRM_Supportcase_Utils_Setting::getCaseManagerRelationshipTypeInfo(CRM_Supportcase_Install_Entity_CaseType::SUPPORT_CASE);

    try {
      $relationship = civicrm_api3('Relationship', 'getsingle', [
        $relationshipTypeInfo['manager_column_name'] => $managerContactId,
        $relationshipTypeInfo['client_column_name'] => $clientContactId,
        'relationship_type_id' => $relationshipTypeInfo['manager_relationship_type_id'],
        'case_id' => $caseId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return $relationship;
  }

}
