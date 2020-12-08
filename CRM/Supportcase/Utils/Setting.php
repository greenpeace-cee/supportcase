<?php

class CRM_Supportcase_Utils_Setting {

  /**
   * Cache for main case type id
   *
   * @var int|null
   */
  private static $mainCaseTypeId = NULL;

  /**
   * Cache for relationship type info which use for case managers
   *
   * @var array
   */
  private static $caseManagerRelationshipTypeInfo = [];

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
   * Gets relationship type id which use for case managers
   * @param $caseType
   * @return int|null
   */
  public static function getCaseManagerRelationshipTypeInfo($caseType) {
    if (empty($caseType)) {
      return NULL;
    }

    if (!key_exists($caseType, self::$caseManagerRelationshipTypeInfo)) {
      $managerRoleData = (new CRM_Case_XMLProcessor_Process())->getCaseManagerRoleId($caseType);
      if (empty($managerRoleData)) {
        self::$caseManagerRelationshipTypeInfo[$caseType] = NULL;
        return self::$caseManagerRelationshipTypeInfo[$caseType];
      }

      $managerRelationshipDirection = substr($managerRoleData, -4);
      $info = [
        'case_type' => $caseType,
        'manager_relationship_type_id' => substr($managerRoleData, 0, -4),
        'manager_relationship_direction' => $managerRelationshipDirection,
        'manager_relationship_type' => [],
        'manager_column_name' => ($managerRelationshipDirection == '_a_b') ? 'contact_id_b' : 'contact_id_a',
        'client_column_name' => ($managerRelationshipDirection == '_a_b') ? 'contact_id_a' : 'contact_id_b',
      ];

      try {
        $relationshipType = civicrm_api3('RelationshipType', 'getsingle', [
          'id' => $info['manager_relationship_type_id'],
          "return" => [
            "id",
            "name_a_b",
            "label_a_b",
            "name_b_a",
            "label_b_a",
            "description",
            "contact_type_a",
            "contact_type_b",
            "is_reserved",
            "is_active",
          ],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        self::$caseManagerRelationshipTypeInfo[$caseType] = NULL;
        return self::$caseManagerRelationshipTypeInfo[$caseType];
      }

      $info['manager_relationship_type'] = $relationshipType;
      self::$caseManagerRelationshipTypeInfo[$caseType] = $info;
    }

    return self::$caseManagerRelationshipTypeInfo[$caseType];
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
    return 1000;
  }

}
