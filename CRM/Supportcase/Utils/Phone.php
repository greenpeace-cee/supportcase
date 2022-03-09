<?php

class CRM_Supportcase_Utils_Phone {

  /**
   * @param $contactId
   * @return array
   */
  public static function getContactPhones($contactId): array {
    try {
      $currentPhones = civicrm_api3('Phone', 'get', [
        'contact_id' => $contactId,
        'options' => ['limit' => 0],
        'return' => ["contact_id", "phone"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    if (empty($currentPhones['values'])) {
      return [];
    }

    $phones = [];
    foreach ($currentPhones['values'] as $phone) {
      $phones[] = $phone['phone'];
    }

    return $phones;
  }

}
