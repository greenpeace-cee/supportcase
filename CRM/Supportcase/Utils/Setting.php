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

  /**
   * Checks if 'Enable Popup Forms'(ajaxPopupsEnabled) setting is checked
   *
   * @return bool
   */
  public static function isPopupFormsEnabled() {
    try {
      $settings = civicrm_api3('Setting', 'get', [
        'sequential' => 1,
        'return' => ["ajaxPopupsEnabled"],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return FALSE;
    }

    return !empty($settings['values'][0]['ajaxPopupsEnabled']) && $settings['values'][0]['ajaxPopupsEnabled'] == 1;
  }

  /**
   * Time(in second).
   * Time which use for locking cases
   * Case will be locked for this time
   */
  public static function getCaseLocTime() {
    return 25;// value in second
  }

  /**
   * Time(in second).
   * When this time is expired dashboard will check if cases has lock.
   */
  public static function getDashboardLockReloadTime() {
    return 15;// value in second
  }

  /**
   * Time(in second).
   * Uses when user is managing case.
   * When this time is expired dashboard will continue locking of open case.
   * This value have to be lower than value from getCaseLocTime() method.
   */
  public static function getMangeCaseUpdateLockTime() {
    return 15;// value in second
  }

  /**
   * This message will show when case is locked by self
   */
  public static function getLockedCaseBySelfMessage() {
    return ts('This case locked by you.');
  }

}
