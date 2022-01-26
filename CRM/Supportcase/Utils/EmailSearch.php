<?php

class CRM_Supportcase_Utils_EmailSearch {

  /**
   * @param $searchString
   * @return array
   */
  public static function searchByString($searchString) {
    if (empty($searchString)) {
      return [];
    }

    $searchResult = [];

    $params = [
      'sequential' => 1,
      'return' => ["contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted', 'contact_id.contact_type'],
      'email' => ['LIKE' => "%" . $searchString . "%"],
      'contact_id.display_name' => ['LIKE' => "%" . $searchString . "%"],
      'options' => [
        'or' => [["contact_id.display_name", "email"]],
        'limit' => 0,
      ],
    ];

    // to make better performance:
    if (CRM_Supportcase_Utils_String::isStringContains('@', $searchString)) {
      unset($params['contact_id.display_name']);
      unset($params['options']['or']);
    }

    try {
      $emails = civicrm_api3('Email', 'get', $params);
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
   * @param $commaSeparatedEmailIds
   * @return array
   */
  public static function searchByCommaSeparatedIds($commaSeparatedEmailIds) {
    $preparedEmails = [];

    try {
      $emails = civicrm_api3('Email', 'get', [
        'sequential' => 1,
        'return' => ["contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted', 'contact_id.contact_type'],
        'id' => ['IN' => explode(',', $commaSeparatedEmailIds)],
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
    $ico = '';
    if ($email['contact_id.contact_type'] == 'Individual') {
      $ico = 'Individual-icon';
    } elseif ($email['contact_id.contact_type'] == 'Organization') {
      $ico = 'Organization-icon';
    } elseif ($email['contact_id.contact_type'] == 'Household') {
      $ico = 'Household-icon';
    }

    return [
      'label' => self::prepareEmailLabel($email['contact_id.display_name'], $email['email']),
      'email' => $email['email'],
      'contact_display_name' => $email['contact_id.display_name'],
      'email_id' => $email['id'],
      'contact_id' => $email['contact_id.id'],
      'icon' => $ico,
      'label_class' => '',
      'description' => [],
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

    return $contactDisplayName . ' <' . $email . '>';
  }

  /**
   * @param $emailLabel
   * @return string
   */
  public static function replaceHtmlSymbolInEmailLabel($emailLabel) {
    if (empty($emailLabel)) {
      return '';
    }

    $emailLabel = str_replace("<", "&lt;", $emailLabel);
    $emailLabel = str_replace(">", "&gt;", $emailLabel);

    return $emailLabel;
  }

  /**
   * @param $contactId
   * @return int|false
   */
  public static function getEmailIdByContactId($contactId) {
    if (empty($contactId)) {
      return false;
    }

    try {
      $email = civicrm_api3('Email', 'getsingle', [
        'sequential' => 1,
        'return' => ['id', "contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted', 'contact_id.contact_type'],
        'contact_id' => $contactId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    $email['email_label'] = self::prepareEmailLabel($email['contact_id.display_name'], $email['email']);

    return $email;
  }

  /**
   * @param $emailId
   * @return int|false
   */
  public static function getContactIdByEmailId($emailId) {
    if (empty($emailId)) {
      return false;
    }

    try {
      $email = civicrm_api3('Email', 'getsingle', [
        'sequential' => 1,
        'return' => ['id', "contact_id.display_name" , 'email', 'contact_id.id', 'contact_id.is_deleted', 'contact_id.contact_type'],
        'id' => $emailId,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    return $email['contact_id.id'];
  }

}
