<?php

class CRM_Supportcase_Utils_Setting {

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

}
