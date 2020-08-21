<?php

class CRM_Supportcase_Utils_OptionGroup {

  /**
   * Gets id
   *
   * @param $optionGroupName
   * @return null|string
   */
  public static function getId($optionGroupName) {
    if (empty($optionGroupName)) {
      return NULL;
    }

    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', [
        'return' => "id",
        'name' => $optionGroupName,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return NULL;
    }

    return $optionGroupId;
  }

}
