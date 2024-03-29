<?php

use Civi\Api4\Note;
use Civi\Api4\Relationship;

class CRM_Supportcase_Utils_DuplicateContacts {

  /**
   * Gets list of duplicate contacts data
   *
   * @param $contactId
   *
   * @return array
   */
  public static function getData($contactId): array {
    $data = [];

    $duplicates = [
      self::searchEmailDuplicates($contactId),
      self::searchPhoneDuplicates($contactId),
      self::searchNameDuplicates($contactId),
    ];

    foreach ($duplicates as $duplicate) {
      if (!empty($duplicate)) {
        $data[] = $duplicate;
      }
    }

    return $data;
  }

  /**
   * @param $searchByContactId
   * @return array
   */
  private static function searchEmailDuplicates($searchByContactId): array {
    $searchEmails = CRM_Supportcase_Utils_Email::getContactEmails($searchByContactId);
    if (empty($searchEmails)) {
      return [];
    }

    $duplicateContactIds = self::searchDuplicateContactIds('Email', [
      'sequential' => 1,
      'return' => ["contact_id", "email"],
      'options' => ['limit' => 0],
      'email' => ['IN' => $searchEmails],
      'contact_id.is_deleted' => FALSE,
    ]);

    return self::prepareResponse($duplicateContactIds, 'Email');
  }

  /**
   * @param $searchByContactId
   * @return array
   */
  private static function searchPhoneDuplicates($searchByContactId): array {
    $searchPhoneNumbers = CRM_Supportcase_Utils_Phone::getContactPhones($searchByContactId);
    if (empty($searchPhoneNumbers)) {
      return [];
    }

    $duplicateContactIds = self::searchDuplicateContactIds('Phone', [
      'sequential' => 1,
      'return' => ["contact_id", "phone"],
      'options' => ['limit' => 0],
      'phone' => ['IN' => $searchPhoneNumbers],
      'contact_id.is_deleted' => FALSE,
    ]);

    return self::prepareResponse($duplicateContactIds, 'Phone');
  }

  /**
   * @param $searchByContactId
   * @return array
   */
  private static function searchNameDuplicates($searchByContactId): array {
    try {
      $currentContact = civicrm_api3('Contact', 'getsingle', [
        'id' => $searchByContactId,
        'return' => ['id', 'first_name', 'last_name'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    if (empty($currentContact['last_name'])) {
      return [];
    }

    $duplicateContactIds = self::searchDuplicateContactIds('Contact', [
      'first_name' => $currentContact['first_name'],
      'last_name' => $currentContact['last_name'],
      'options' => ['limit' => 0],
      'return' => ['id', 'contact_id'],
    ]);

    return self::prepareResponse($duplicateContactIds, 'Name');
  }

  /**
   * @param string $entityName
   * @param array $entityParams
   * @return array
   */
  private static function searchDuplicateContactIds(string $entityName, array $entityParams): array {
    try {
      $duplicateEntities = civicrm_api3($entityName, 'get', $entityParams);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    if (empty($duplicateEntities['values'])) {
      return [];
    }

    $duplicateContactIds = [];
    foreach ($duplicateEntities['values'] as $entity) {
      $duplicateContactIds[] = (int) $entity['contact_id'];
    }

    return array_unique($duplicateContactIds);
  }

  /**
   * @param $duplicateContactIds
   * @param $label
   * @return array
   */
  private static function prepareResponse($duplicateContactIds, $label): array {
    if (empty($duplicateContactIds) || empty($label) || count($duplicateContactIds) == 1) {
      return [];
    }

    return [
      'label' => $label,
      'link' => CRM_Utils_System::url('civicrm/contact/search/builder', [
        'reset' => '1',
        'c_ids' => implode(',', $duplicateContactIds),
        'spc_is_use_prefill_val' => '1',
      ], FALSE, NULL, FALSE),
      'count' => count($duplicateContactIds),
    ];
  }

  /**
   * Create (or update) a duplicate relationship between $leadingContact and
   * $dupeContact
   *
   * @param int $caseId
   * @param int $leadingContact
   * @param int $dupeContact
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public static function createDuplicateRelationship(
    int $caseId,
    int $leadingContact,
    int $dupeContact
  ) {
    $note = 'Merge requested in Support Case #' . $caseId;
    $relationship = Relationship::get(FALSE)
      ->addSelect('note.note', 'note.id', 'id')
      ->setJoin([
        ['Note AS note', 'LEFT', NULL, ['note.entity_table', '=', "'civicrm_relationship'"], ['note.entity_id', '=', 'id']],
      ])
      ->addWhere('relationship_type_id:name', '=', 'duplicates')
      ->addClause('OR',
        ['AND', [['contact_id_a', '=', $dupeContact], ['contact_id_b', '=', $leadingContact]]],
        ['AND', [['contact_id_a', '=', $leadingContact], ['contact_id_b', '=', $dupeContact]]]
      )
      ->execute()
      ->first();
    if (!empty($relationship['id'])) {
      Relationship::update(FALSE)
        ->addWhere('id', '=', $relationship['id'])
        ->addValue('is_active', TRUE)
        ->addValue('contact_id_a', $dupeContact)
        ->addValue('contact_id_b', $leadingContact)
        ->execute();
      Note::update(FALSE)
        ->addWhere('id', '=', $relationship['note.id'])
        ->addValue('note', trim(
          $relationship['note.note'] . '\n' . $note
        ))
        ->execute();
    }
    else {
      Relationship::create(FALSE)
        ->addValue('contact_id_a', $dupeContact)
        ->addValue('contact_id_b', $leadingContact)
        ->addValue('relationship_type_id:name', 'duplicates')
        ->addValue('start_date', 'now')
        ->addChain('note', Note::create()
          ->addValue('note', $note)
          ->addValue('entity_table:name', 'Relationship')
          ->addValue('entity_id', '$id')
        )
        ->execute();
    }
  }

}
