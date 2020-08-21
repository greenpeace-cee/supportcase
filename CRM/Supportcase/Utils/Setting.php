<?php

class CRM_Supportcase_Utils_Setting {

  /**
   * Cache for main case type id
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
   * Gets id of main case type
   *
   * @return integer|null
   */
  public static function getMainCaseTypeId() {
    if (is_null(self::$mainCaseTypeId)) {
      try {
        $supportCaseTypeId = civicrm_api3('CaseType', 'getvalue', [
          'return' => 'id',
          'name' => CRM_Supportcase_Install_Entity_CaseType::SUPPORT_CASE,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        return NULL;
      }

      self::$mainCaseTypeId = $supportCaseTypeId;
    }

    return self::$mainCaseTypeId;
  }

  /**
   * Gets available case statuses based on extension setting(supportcase_available_case_status_names)
   *
   * @return array
   */
  public static function getCaseStatusOptions() {
    $availableCaseStatuses = CRM_Supportcase_Utils_Setting::get('supportcase_available_case_status_names');

    try {
      $caseStatuses = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'option_group_id' => "case_status",
        'name' => ['IN' => $availableCaseStatuses],
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    return $caseStatuses['values'];
  }

}
