<?php

class CRM_Supportcase_Utils_PartyType {

  /**
   * Cache for party types
   *
   * @var array
   */
  private static $partyTypesCache = null;

  /**
   * Mailutils message party types
   *
   * @var string
   */
  const FROM = 'from';
  const TO = 'to';
  const CC = 'cc';
  const BCC = 'bcc';

  /**
   * @param $partyType
   * @return bool
   */
  public static function isExistPartyType($partyType): bool {
    return in_array($partyType, [
      CRM_Supportcase_Utils_PartyType::FROM,
      CRM_Supportcase_Utils_PartyType::TO,
      CRM_Supportcase_Utils_PartyType::CC,
      CRM_Supportcase_Utils_PartyType::BCC,
    ]);
  }

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
    return self::getPartyTypeIdByName(CRM_Supportcase_Utils_PartyType::FROM);
  }

  /**
   * @return array|false
   */
  public static function getToPartyTypeId() {
    return self::getPartyTypeIdByName(CRM_Supportcase_Utils_PartyType::TO);
  }

  /**
   * @return array|false
   */
  public static function getCcPartyTypeId() {
    return self::getPartyTypeIdByName(CRM_Supportcase_Utils_PartyType::CC);
  }

  /**
   * @return array|false
   */
  public static function getBccPartyTypeId() {
    return self::getPartyTypeIdByName(CRM_Supportcase_Utils_PartyType::BCC);
  }

}
