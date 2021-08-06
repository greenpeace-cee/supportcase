<?php

class CRM_Supportcase_Utils_EmailSearch {

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
    $searchResult = array_merge($searchResult, self::searchByStringGroups($searchString));
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
          'return' => ["email", "id"],
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
  public static function searchByStringGroups($searchString) {
    $searchResult = [];

    try {
      $groups = civicrm_api3('Group', 'get', [
          'sequential' => 1,
          'return' => ["title", "id"],
          'options' => ['limit' => 0],
          "is_hidden" =>  "0",
          "is_active" =>  "1",
          'title' => ['LIKE' => "%" . $searchString . "%"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $searchResult;
    }

    if (!empty($groups['values'])) {
      foreach ($groups['values'] as $group) {
        $searchResult[] = self::prepareGroupResponse($group);
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
          'return' => ["display_name", "id"],
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

    if (empty($entityId) || (empty($entity) && in_array($entity, ['Contact', 'Group', 'Email']))) {
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
      try {
        $result = civicrm_api3($entity, 'getsingle', $params);
        $searchResult[] = self::prepareContactResponse($result);
      } catch (CiviCRM_API3_Exception $e) {}
    }

    if ($entity == 'Group') {
      $params['return'][] = 'title';
      try {
        $result = civicrm_api3($entity, 'getsingle', $params);
        $searchResult[] = self::prepareGroupResponse($result);
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
   * @param $group
   * @return array
   */
  private static function prepareGroupResponse($group) {
    return [
        'entity' => 'Group',
        'entity_id' => $group['id'],
        'label' => 'Group: ' . $group['title'],
        'id' => $group['id'] . self::SEPARATOR . 'Group',
    ];
  }

  /**
   * @param $email
   * @return array
   */
  private static function prepareEmailResponse($email) {
    return [
        'entity' => 'Email',
        'entity_id' => $email['id'],
        'label' => 'Email: ' . $email['email'],
        'id' => $email['id'] . self::SEPARATOR . 'Email',
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
        'label' => 'Contact: ' . $contact['display_name'],
        'id' => $contact['id'] . self::SEPARATOR . 'Contact',
    ];
  }

}
