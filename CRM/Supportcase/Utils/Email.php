<?php

class CRM_Supportcase_Utils_Email {

  /**
   * Forward mode name
   *
   * @var string
   */
  const FORWARD_MODE = 'forward';

  /**
   * Reply mode name
   *
   * @var string
   */
  const REPLY_MODE = 'reply';

  /**
   * Reply mode name
   *
   * @var string
   */
  const REPLY_ALL_MODE = 'reply_all';

  /**
   * New email name
   *
   * @var string
   */
  const NEW_EMAIL_MODE = 'new';

  /**
   * @return array
   */
  public static function getModeOptions() {
    return [
      self::FORWARD_MODE => 'Forward mode',
      self::REPLY_MODE => 'Reply mode',
      self::NEW_EMAIL_MODE => 'New email mode',
      self::REPLY_ALL_MODE => 'Reply all mode',
    ];
  }

  /**
   * @return string[]
   */
  public static function getAvailableModes() {
    return array_keys(self::getModeOptions());
  }

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
    return !empty(self::getLocationType($locationTypeName));
  }

  /**
   * Get location type
   *
   * @param $locationTypeName
   * @return array|bool
   */
  public static function getLocationType($locationTypeName) {
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

    return $locationType;
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

  /**
   * @param $subject
   * @param null $mode
   * @return string
   */
  public static function addSubjectPrefix($subject, $mode = null) {
    if ($mode === CRM_Supportcase_Utils_Email::FORWARD_MODE) {
      return "Fwd: {$subject}";
    }

    if ($mode === CRM_Supportcase_Utils_Email::REPLY_MODE) {
      return "Re: {$subject}";
    }

    return $subject;
  }

  /**
   * @param $emailIds
   * @return array
   */
  public static function getEmailsByIds($emailIds) {
    $emailNames = [];
    try {
      $emails = civicrm_api3('Email', 'get', [
        'sequential' => 1,
        'return' => ["email", 'id'],
        'id' => ['IN' => $emailIds],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    foreach ($emails['values'] as $email) {
      $emailNames[$email['id']] = $email['email'];
    }

    return $emailNames;
  }

  /**
   * @param $email
   * @return int|null
   */
  public static function getEmailId($email, $contactId = null) {
    if (empty($email)) {
      return null;
    }

    $params = [
      'sequential' => 1,
      'email' => $email,
    ];

    if (!empty($contactId)) {
      $params['contact_id'] = $contactId;
    }

    try {
      $emails = civicrm_api3('Email', 'get', $params);
    } catch (CiviCRM_API3_Exception $e) {
      return null;
    }

    foreach ($emails['values'] as $email) {
      return $email['id'];
    }

    return null;
  }

  /**
   * @param $email
   * @param $contactId
   * @return array
   */
  public static function getEmailContactData($email, $contactId) {
    if (empty($email) || empty($contactId)) {
      return [];
    }

    try {
      $emails = civicrm_api3('Email', 'get', [
        'sequential' => 1,
        'return' => ["contact_id.display_name", 'email', 'id', 'contact_id', "contact_id.contact_type"],
        'email' => $email,
        'contact_id' => $contactId,
        'options' => ['limit' => 1],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return [];
    }

    foreach ($emails['values'] as $email) {
      return [
        'contact_display_name' => $email["contact_id.display_name"],
        'contact_type' => $email["contact_id.contact_type"],
        'email' => $email["email"],
        'contact_id' => $email["contact_id"],
        'id' => $email["id"],
      ];
    }

    return [];
  }

}
