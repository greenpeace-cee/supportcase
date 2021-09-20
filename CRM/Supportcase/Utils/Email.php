<?php

class CRM_Supportcase_Utils_Email {

  /**
   * Checks if email is valid
   *
   * @param $stringEmail
   * @return bool
   */
  public static function isValidEmail($stringEmail) {
    $pattern = '/^((\"[^\"\f\n\r\t\v\b]+\")|([A-Za-z0-9_\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[A-Za-z0-9_\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]{2,}))$/';

    if (strlen($stringEmail) > 255) {
      return false;
    }

    return !preg_match( $pattern, $stringEmail);
  }

  /**
   * Checks if location type exist
   *
   * @param $locationTypeName
   * @return bool
   */
  public static function isLocationTypeExist($locationTypeName) {
    if (empty($locationTypeName)) {
      return false;
    }

    try {
      $locationType = civicrm_api3('LocationType', 'getsingle', [
          'name' => $locationTypeName,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    return !empty($locationType['id']);
  }

  /**
   * Checks if location type exist
   *
   * @param $subject
   * @return string
   */
  public static function normalizeEmailSubject($subject) {
    if (empty($subject)) {
      return '';
    }

    if (!CRM_Supportcase_Utils_Setting::isMailUtilsExtensionEnable()) {
      return '';
    }

    return \Civi\Mailutils\SubjectNormalizer::normalize($subject);
  }

}
