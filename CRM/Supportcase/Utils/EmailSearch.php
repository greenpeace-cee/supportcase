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

    $preparedEmails = [];

    try {
      $emailQuery = self::getEmailSearchObject();

      // to make better performance:
      if (CRM_Supportcase_Utils_String::isStringContains('@', $searchString)) {
        $emailQuery->addWhere('email', 'LIKE', "%" . $searchString . "%");
      } else {
        $emailQuery->addClause('OR', ['contact.display_name', 'LIKE', "%" . $searchString . "%"], ['email', 'LIKE', "%" . $searchString . "%"]);
      }

      $emailsData = $emailQuery->execute();
    } catch (Exception $e) {
      return $preparedEmails;
    }

    foreach ($emailsData as $emailData) {
      $preparedEmails[] = self::prepareResponse($emailData);
    }

    return $preparedEmails;
  }

  /**
   * @return mixed
   */
  private static function getEmailSearchObject() {
    return \Civi\Api4\Email::get(FALSE)
      ->addSelect(
        'email',
        'contact.first_name',
        'contact.last_name',
        'contact.display_name',
        'contact.id',
        'contact.is_deleted',
        'contact.contact_type',
        'contact.addressee_display'
      )
      ->setJoin([
        ['Contact AS contact', 'LEFT', NULL, ['contact_id', '=', 'contact.id']],
      ]);
  }

  /**
   * @param $commaSeparatedEmailIds
   * @return array
   */
  public static function searchByCommaSeparatedIds($commaSeparatedEmailIds) {
    $preparedEmails = [];
    $emailIds = explode(',', $commaSeparatedEmailIds);

    try {
      $emailsData = self::getEmailSearchObject()
        ->addWhere('id', 'IN', $emailIds)
        ->execute();
    } catch (Exception $e) {
      return $preparedEmails;
    }

    foreach ($emailsData as $emailData) {
      $preparedEmails[] = self::prepareResponse($emailData);
    }

    return $preparedEmails;
  }

  /**
   * @param $email
   * @return array
   */
  private static function prepareResponse($email) {
    $ico = '';
    if ($email['contact.contact_type'] == 'Individual') {
      $ico = 'Individual-icon';
    } elseif ($email['contact.contact_type'] == 'Organization') {
      $ico = 'Organization-icon';
    } elseif ($email['contact.contact_type'] == 'Household') {
      $ico = 'Household-icon';
    }

    if (!empty($email['contact.addressee_display'])) {
      $customName = $email['contact.addressee_display'];
    } else {
      $customName = $email['contact.display_name'];
    }

    return [
      'label' => self::prepareEmailLabel($email['contact.display_name'], $email['email']),
      'email' => $email['email'],
      'contact_display_name' => $email['contact.display_name'],
      'contact_custom_display_name' => $customName,
      'email_id' => $email['id'],
      'contact_id' => $email['contact.id'],
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

    $emailLabel = str_replace("<", '[', $emailLabel);
    $emailLabel = str_replace(">", ']', $emailLabel);

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
