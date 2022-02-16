<?php

class CRM_Supportcase_Utils_Setting {

  /**
   * Cache for main case type id
   *
   * @var int|null
   */
  private static $mainCaseTypeId = NULL;

  /**
   * Cache is checking if extensions is enabled
   *
   * @var array
   */
  private static $isEnableExtensions = [];

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
        'options' => [
          'limit' => 0,
          'sort' => 'weight',
        ],
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
    return 15;// value in second
  }

  /**
   * Time(in second).
   * When this time is expired dashboard will check if cases has lock.
   */
  public static function getDashboardLockReloadTime() {
    return 10;// value in second
  }

  /**
   * Time(in second).
   * Uses when user is managing case.
   * When this time is expired dashboard will continue locking of open case.
   * This value have to be lower than value from getCaseLocTime() method.
   */
  public static function getMangeCaseUpdateLockTime() {
    return 10;// value in second
  }

  /**
   * This message will show when case is locked by self
   */
  public static function getLockedCaseBySelfMessage() {
    return ts('This case locked by you.');
  }

  /**
   * Default count of rows on supportcase dashboard
   */
  public static function getDefaultCountOfRows() {
    return 100;
  }

  /**
   * CaseLock row live time
   * Use on api3: CaseLock->clean_old
   * When CaseLock has 'lock_expire_at' less than (current timestamp - this setting)
   * then this row will be removed.
   */
  public static function getCaseLockRowLiveTime() {
    return 60 * 60 * 24 * 3;// value in second
  }

  /**
 * Is extension enabled
 * check inc cache
 *
 * @param $extensionName
 * @return bool
 */
  public static function isExtensionEnable($extensionName) {
    if (empty($extensionName)) {
      return FALSE;
    }

    if (!isset(self::$isEnableExtensions[$extensionName])) {
      self::$isEnableExtensions[$extensionName] = self::isExtensionEnableDatabaseCheck($extensionName);
    }

    return self::$isEnableExtensions[$extensionName];
  }

  /**
   * Is extension enabled
   * check in database
   *
   * @param $extensionName
   * @return bool
   */
  private static function isExtensionEnableDatabaseCheck($extensionName) {
    if (empty($extensionName)) {
      return FALSE;
    }

    try {
      $extensionStatus = civicrm_api3('Extension', 'getsingle', [
        'return' => "status",
        'full_name' => $extensionName,
      ]);
    } catch (Exception $e) {
      return FALSE;
    }

    if ($extensionStatus['status'] == 'installed') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Is "at.greenpeace.casetools" enabled
   *
   * @return bool
   */
  public static function isCaseToolsExtensionEnable() {
    return self::isExtensionEnable('at.greenpeace.casetools');
  }

  /**
   * Is "mailutils" enabled
   *
   * @return bool
   */
  public static function isMailUtilsExtensionEnable() {
    return self::isExtensionEnable('mailutils');
  }

  /**
   * Gets groups data which is used like a subscription.
   * Used on angular page(Manage case -> actions -> manage email subscriptions)
   *
   * @return array
   */
  public static function getSubscriptionsGroups() {
    $groupIds = CRM_Supportcase_Utils_Setting::get('supportcase_subscription_group_ids');
    if (empty($groupIds)) {
      return [];
    }

    try {
      $groups = civicrm_api3('Group', 'get', [
        'sequential' => 1,
        'options' => ['limit' => 0],
        'id' => ['IN' => $groupIds],
        'is_active' => 1,
        'return' => ["id", "description", "title", "name"],
      ]);
    } catch (Exception $e) {
      return [];
    }

    return !empty($groups['values']) ? $groups['values'] : [];
  }

  public static function getAvailableActivityTypeIds() {
    $activityTypeNames = CRM_Supportcase_Utils_Setting::get('supportcase_available_activity_type_names');
    $activityTypeIds = [];

    try {
      $optionValues = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'options' => ['limit' => 0],
        'option_group_id' => "activity_type",
        'name' => ['IN' => $activityTypeNames],
      ]);
    } catch (Exception $e) {
      return $activityTypeIds;
    }

    if (!empty($optionValues['values'])) {
      foreach ($optionValues['values'] as $activityType) {
        $activityTypeIds[] = (int) $activityType['value'];
      }
    }

    return $activityTypeIds;
  }

  /**
   * @return int
   */
  public static function getMaxFilesSize() {
    $config = CRM_Core_Config::singleton();

    return $config->maxFileSize ? $config->maxFileSize : 3;
  }

  /**
   * @return int
   */
  public static function getActivityAttachmentLimit() {
    $config = CRM_Core_Config::singleton();

    return $config->maxAttachments ? $config->maxAttachments : 3;
  }

}
