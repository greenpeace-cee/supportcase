<?php

class CRM_Supportcase_Utils_MailutilsMessage {

  /**
   * @param $mailSettingId
   * @return array|null
   */
  public static function getRelatedMailUtilsSetting($mailSettingId) {
    if (empty($mailSettingId)) {
      return null;
    }

    $mailutilsSettings = \Civi\Api4\MailutilsSetting::get()
      ->addWhere('mail_setting_id', '=', $mailSettingId)
      ->setLimit(1)
      ->execute();
    foreach ($mailutilsSettings as $mailutilsSetting) {
      return $mailutilsSetting;
    }

    return null;
  }

}
