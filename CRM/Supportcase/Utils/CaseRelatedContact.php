<?php

class CRM_Supportcase_Utils_CaseRelatedContact {

  /**
   * Cache for relationship type id
   *
   * @var int|null
   */
  private static $relationshipTypeId = NULL;

  /**
   * @param $caseId
   * @return array
   */
  public static function get($caseId, $clientIds): array {
    $data = [
      'case_id' => $caseId,
      'contact_id_a' => '',
      'related_contact_ids' => [],
    ];

    if (empty($caseId)) {
      return $data;
    }

    if (empty($clientIds) || empty($clientIds[0])) {
      return $data;
    }

    $contactIdA = $clientIds[0];
    $data['contact_id_a'] = $contactIdA;
    $data['related_contact_ids'] = self::getRelatedContactIds($caseId, $contactIdA);

    return $data;
  }

  /**
   * @param $caseId
   * @param $newRelatedContactIds
   * @param $relatedContactIdA
   * @return void
   */
  public static function update($caseId, $newRelatedContactIds, $relatedContactIdA) {
    if (empty($caseId) || empty($relatedContactIdA)) {
      return;
    }

    $relationshipTypeId = self::getRelationshipTypeId();
    if (empty($relationshipTypeId)) {
      return;
    }

    $currentRelatedContactIds = self::getRelatedContactIds($caseId, $relatedContactIdA);

    foreach ($newRelatedContactIds as $index => $value) {
      $newRelatedContactIds[$index] = (int) $value;
    }

    $needToAddContactIds = array_diff($newRelatedContactIds, $currentRelatedContactIds);
    $needToDeleteContactIds = array_diff($currentRelatedContactIds, $newRelatedContactIds);


    foreach ($needToDeleteContactIds as $relatedContactIdB) {
      self::deleteRelationship($caseId, $relatedContactIdA, $relatedContactIdB);
    }

    foreach ($needToAddContactIds as $relatedContactIdB) {
      self::createRelationship($caseId, $relatedContactIdA, $relatedContactIdB);
    }
  }

  /**
   * @param $caseId
   * @param $contactIdA
   * @return array
   */
  private static function getRelatedContactIds($caseId, $contactIdA): array {
    $relationships = \Civi\Api4\Relationship::get(FALSE)
      ->addWhere('case_id', '=', $caseId)
      ->addWhere('contact_id_a', '=', $contactIdA)
      ->addWhere('relationship_type_id', '=', self::getRelationshipTypeId())
      ->setCurrent(TRUE)
      ->execute();

    $contactIds = [];

    foreach ($relationships as $relationship) {
      $contactIds[] = (int) $relationship['contact_id_b'];
    }

    return $contactIds;
  }

  /**
   * Get relationship type id from cache
   *
   * @return false|int
   */
  public static function getRelationshipTypeId() {
    if (is_null(self::$relationshipTypeId)) {
      $relationshipTypeId = self::getFromDBRelationshipTypeId();

      self::$relationshipTypeId = $relationshipTypeId;
    }

    return self::$relationshipTypeId;
  }

  /**
   * Get relationship type id from database
   *
   * @return false|int
   */
  private static function getFromDBRelationshipTypeId() {
    try {
      $relationshipType = civicrm_api3('RelationshipType', 'getsingle', [
        'return' => ["id"],
        'name_a_b' => CRM_Supportcase_Install_Entity_RelationshipType::MADE_SUPPORT_REQUEST_RELATED_TO,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    return (int) $relationshipType['id'];
  }

  /**
   * @param $caseId
   * @param $relatedContactIdA
   * @param $relatedContactIdB
   * @return void
   */
  private static function deleteRelationship($caseId, $relatedContactIdA, $relatedContactIdB) {
    $results = \Civi\Api4\Relationship::delete(FALSE)
      ->addWhere('contact_id_a', '=', $relatedContactIdA)
      ->addWhere('contact_id_b', '=', $relatedContactIdB)
      ->addWhere('relationship_type_id', '=', self::getRelationshipTypeId())
      ->addWhere('case_id', '=', $caseId)
      ->execute();
  }

  /**
   * @param $caseId
   * @param $relatedContactIdA
   * @param $relatedContactIdB
   * @return void
   */
  private static function createRelationship($caseId, $relatedContactIdA, $relatedContactIdB) {
    $results = \Civi\Api4\Relationship::create(FALSE)
      ->addValue('contact_id_a', $relatedContactIdA)
      ->addValue('contact_id_b', $relatedContactIdB)
      ->addValue('relationship_type_id', self::getRelationshipTypeId())
      ->addValue('case_id', $caseId)
      ->execute();
  }

}
