<?php

class CRM_Supportcase_Utils_PartyType {

  /**
   * Cache for party types
   *
   * @var array
   */
  private static $partyTypesCache = null;

  /**
   * @return array
   */
  public static function getAvailablePartyTypes() {
    if (is_null(self::$partyTypesCache)) {
      $partyTypes = CRM_Supportcase_Utils_OptionValue::getByOptionGroup('mailutils_party_type');
      $preparedPartyTypes = [];
      foreach ($partyTypes as $partyType) {
        $preparedPartyTypes[$partyType['name']] = $partyType;
      }

      self::$partyTypesCache = $preparedPartyTypes;
    }

    return self::$partyTypesCache;
  }

  /**
   * @param $partyTypeName
   * @return false|array
   */
  public static function getPartyTypeIdByName($partyTypeName) {
    $partyTypes = self::getAvailablePartyTypes();

    return !empty($partyTypes[$partyTypeName]['value']) ? $partyTypes[$partyTypeName]['value'] : false;
  }

  /**
   * @return array|false
   */
  public static function getFromPartyTypeId() {
    return self::getPartyTypeIdByName('from');
  }

  /**
   * @return array|false
   */
  public static function getToPartyTypeId() {
    return self::getPartyTypeIdByName('to');
  }

  /**
   * @return array|false
   */
  public static function getCcPartyTypeId() {
    return self::getPartyTypeIdByName('cc');
  }

  /**
   * @return array|false
   */
  public static function getBccPartyTypeId() {
    return self::getPartyTypeIdByName('bcc');
  }

}
