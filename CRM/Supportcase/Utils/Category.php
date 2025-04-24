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
        'name' => CRM_Supportcase_Install_Entity_OptionGroup::CASE_CATEGORY,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    try {
      $categories = civicrm_api3('OptionValue', 'get', [
        'return' => ["id", "value", "label", "icon"],
        "sequential" => 1,
        'option_group_id' => $optionGroupId,
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return $categories['values'];
  }

  /**
   * Gets list of categories(value => label)
   *
   * @return array
   */
  public static function getOptions() {
    $categoryOptions = [];
    foreach (self::get() as $category) {
      $categoryOptions[$category['value']] = $category['label'];
    }

    return $categoryOptions;
  }

}
