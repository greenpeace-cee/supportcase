<?php

class CRM_Supportcase_Utils_String {

  /**
   * @param $searchByString
   * @param $targetString
   * @return bool
   */
  public static function isStringContains($searchByString, $targetString) {
    $pos = strpos($targetString, $searchByString);

    if ($pos === false) {
      return false;
    }

    return true;
  }

}
