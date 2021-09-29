<?php

class CRM_Supportcase_Utils_OptionValue {

  /**
   * Gets labels by value
   *
   * @param $value
   * @param $optionGroupName
   * @return array|string
   */
  public static function getLabelByValue($value, $optionGroupName) {
    $label = '';
    if (empty($value) || empty($optionGroupName)) {
      return $label;
    }

    try {
      $label = civicrm_api3('OptionValue', 'getvalue', [
        'return' => "label",
        'option_group_id' => $optionGroupName,
        'value' => $value,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return $label;
    }

    return $label;
  }

  /**
   * @param $optionGroupName
   * @return array
   */
  public static function getByOptionGroup($optionGroupName) {
    if (empty($optionGroupName)) {
      return [];
    }

    try {
      $optionValues = civicrm_api3('OptionValue', 'get', [
        'return' => ["label", "value", "name", "is_active"],
        'option_group_id' => $optionGroupName,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return $optionValues['values'];
  }

}
