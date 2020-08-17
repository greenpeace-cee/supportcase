<?php

class CRM_Supportcase_Utils_Setting {

  /**
   * Cache for case type id where name = 'support_case'
   *
   * @var int|null
   */
  private static $mainCaseTypeId = NULL;

  /**
   * Gets Supportcase setting by name
   *
   * @param $settingName
   * @return array|null
   */
  public static function get($settingName) {
    try {
      $setting = civicrm_api3('Setting', 'getvalue', [
        'name'  => $settingName,
        'group' => 'SupportcaseConfig'
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return NULL;
    }

    return $setting;
  }

  /**
   * Gets id of main case type('support_case')
   *
   * @return integer|null
   */
  public static function getMainCaseTypeId() {
    if (is_null(self::$mainCaseTypeId)) {
      try {
        $supportCaseTypeId = civicrm_api3('CaseType', 'getvalue', [
          'return' => 'id',
          'name' => 'support_case',
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        return NULL;
      }

      self::$mainCaseTypeId = $supportCaseTypeId;
    }

    return self::$mainCaseTypeId;
  }

}
