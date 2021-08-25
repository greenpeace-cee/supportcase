<?php

class CRM_Supportcase_Utils_EmailSearch {

  /**
   * Value separator
   *
   * @var string
   */
  const SEPARATOR = '_';

  /**
   * @return array
   */
  public static function searchByString($searchString) {
    $searchResult = [];

    if (empty($searchString)) {
      return $searchResult;
    }

    $searchResult = array_merge($searchResult, self::searchByStringEmails($searchString));
    $searchResult = array_merge($searchResult, self::searchByStringContacts($searchString));

    return $searchResult;
  }

  /**
   * @return array
   */
  public static function searchByStringEmails($searchString) {
    $searchResult = [];

    try {
      $emails = civicrm_api3('Email', 'get', [
          'sequential' => 1,
          'return' => ["email", "id", "contact_id.display_name"],
          'options' => ['limit' => 0],
          "is_primary" => "1",
          "on_hold" => "0",
          "is_bulkmail" => "0",
          'email' => ['LIKE' => "%" . $searchString . "%"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $searchResult;
    }

    if (!empty($emails['values'])) {
      foreach ($emails['values'] as $email) {
        $searchResult[] = self::prepareEmailResponse($email);
      }
    }

    return $searchResult;
  }

  /**
   * @return array
   */
  public static function searchByStringContacts($searchString) {
    $searchResult = [];

    try {
      $contacts = civicrm_api3('Contact', 'get', [
          'sequential' => 1,
          'return' => ["display_name", "id", "email"],
          'options' => ['limit' => 0],
          'is_deleted' => "0",
          'display_name' => ['LIKE' => "%" . $searchString . "%"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $searchResult;
    }

    if (!empty($contacts['values'])) {
      foreach ($contacts['values'] as $contact) {
        $searchResult[] = self::prepareContactResponse($contact);
      }
    }

    return $searchResult;
  }

  /**
   * @return array
   */
  public static function searchByPseudoId($searchPseudoId) {
    $searchResult = [];
    $parts = explode(self::SEPARATOR, $searchPseudoId);
    $entityId = $parts[0];
    $entity = $parts[1];

    if (empty($entityId) || (empty($entity) && in_array($entity, ['Contact', 'Email']))) {
      return [
        [
          'entity' => 'unknown',
          'entity_id' => 'unknown',
          'label' => 'unknown',
          'id' => 'unknown',
        ]
      ];
    }

    $params = [
      'sequential' => 1,
      'return' => ["id"],
      'options' => ['limit' => 0],
      'id' => $entityId
    ];

    if ($entity == 'Contact') {
      $params['return'][] = 'display_name';
      $params['return'][] = 'email';
      try {
        $result = civicrm_api3($entity, 'getsingle', $params);
        $searchResult[] = self::prepareContactResponse($result);
      } catch (CiviCRM_API3_Exception $e) {}
    }

    if ($entity == 'Email') {
      $params['return'][] = 'email';
      try {
        $result = civicrm_api3($entity, 'getsingle', $params);
        $searchResult[] = self::prepareEmailResponse($result);
      } catch (CiviCRM_API3_Exception $e) {}
    }

    if (empty($searchResult)) {
      $searchResult[] =         [
          'entity' => $entity,
          'entity_id' => $entityId,
          'label' => 'unknown: ' . $searchPseudoId,
          'id' => $searchPseudoId,
      ];
    }

    return $searchResult;
  }

  /**
   * @param $email
   * @return array
   */
  private static function prepareEmailResponse($email) {
    return [
        'entity' => 'Email',
        'entity_id' => $email['id'],
        'label' => 'Email : ' . $email['contact_id.display_name'] . '<' . $email['email'] . '>',
        'id' => self::prepareValue($email['id'], 'Email'),
    ];
  }

  /**
   * @param $contact
   * @return array
   */
  private static function prepareContactResponse($contact) {
    return [
        'entity' => 'Contact',
        'entity_id' => $contact['id'],
        'label' => 'Contact : ' . $contact['display_name'] . '<' . $contact['email'] . '>',
        'id' => self::prepareValue($contact['id'], 'Contact'),
    ];
  }

  /**
   * @param $entityId
   * @param $entityName
   * @return string
   */
  public static function prepareValue($entityId, $entityName) {
    if (empty($entityId) || empty($entityName)) {
      return '';
    }

    return $entityId . self::SEPARATOR . $entityName;
  }

}
