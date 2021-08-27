<?php

class CRM_Supportcase_Utils_EmailSearch {

  /**
   * @param $searchString
   * @return array
   */
  public static function searchByString($searchString) {
    $searchResult = [];

    try {
      $emails = civicrm_api3('Email', 'get', [
        'sequential' => 1,
        'return' => ["contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted'],
        'contact_id.is_deleted' => "0",
        "on_hold" => "0",
        "is_bulkmail" => "0",
        'email' => ['LIKE' => "%" . $searchString . "%"],
        'contact_id.display_name' => ['LIKE' => "%" . $searchString . "%"],
        'options' => [
          'or' => [["contact_id.display_name", "email"]],
          'limit' => 0,
        ],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $searchResult;
    }

    if (!empty($emails['values'])) {
      foreach ($emails['values'] as $email) {
        $searchResult[] = self::prepareResponse($email);
      }
    }

    return $searchResult;
  }

  /**
   * @param $emailId
   * @return array
   */
  public static function searchByIds($emailIds) {
    $preparedEmails = [];

    try {
      $emails = civicrm_api3('Email', 'get', [
        'sequential' => 1,
        'return' => ["contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted'],
        'id' => ['IN' => explode(',', $emailIds)],
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $preparedEmails;
    }

    if (!empty($emails['values'])) {
      foreach ($emails['values'] as $email) {
        $preparedEmails[] = self::prepareResponse($email);
      }
    }

    return $preparedEmails;
  }

  /**
   * @param $email
   * @return array
   */
  private static function prepareResponse($email) {
    return [
      'label' => self::prepareEmailLabel($email['contact_id.display_name'], $email['email']),
      'email' => $email['email'],
      'contact_display_name' => $email['contact_id.display_name'],
      'email_id' => $email['id'],
      'contact_id' => $email['contact_id.id'],
    ];
  }

  /**
   * @param $contactDisplayName
   * @param $email
   * @return string
   */
  public static function prepareEmailLabel($contactDisplayName, $email) {
    if (empty($contactDisplayName) || empty($email)) {
      return '';
    }

    return $contactDisplayName . '<' . $email . '>';
  }

}
