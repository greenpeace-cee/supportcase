<?php

class CRM_Supportcase_Utils_Category {

  /**
   * Gets list of categories
   *
   * @return array
   */
  public static function get() {
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', [
        'return' => "id",
        'name' => "support_case_category",
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    try {
      $categories = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => $optionGroupId,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return $categories['values'];
  }

}
