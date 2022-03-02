<?php

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
    return $data;//TODO: implement searching

    $emailDuplicates = self::searchEmailDuplicates();
    $phoneDuplicates = self::searchPhoneDuplicates();
    $nameDuplicates = self::searchNameDuplicates();

    if (!empty($emailDuplicates)) {
      $data[] = $emailDuplicates;
    }
    if (!empty($phoneDuplicates)) {
      $data[] = $phoneDuplicates;
    }
    if (!empty($nameDuplicates)) {
      $data[] = $nameDuplicates;
    }

    return $data;
  }

  /**
   * @return array
   */
  private static function searchEmailDuplicates(): array {
    return [
      'label' => 'Email',
      'link' => CRM_Utils_System::url('civicrm/contact/search/advanced', [
        'reset' => '1',
      ], FALSE, NULL, FALSE),
      'count' => '2',
    ];
  }

  /**
   * @return array
   */
  private static function searchPhoneDuplicates(): array {
    return [
      'label' => 'Phone',
      'link' => CRM_Utils_System::url('civicrm/contact/search/advanced', [
        'reset' => '1',
      ], FALSE, NULL, FALSE),
      'count' => '2',
    ];
  }

  /**
   * @return array
   */
  private static function searchNameDuplicates(): array {
    return [
      'label' => 'Name',
      'link' => CRM_Utils_System::url('civicrm/contact/search/advanced', [
        'reset' => '1',
      ], FALSE, NULL, FALSE),
      'count' => '2',
    ];
  }

}
