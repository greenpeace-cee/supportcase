<?php

class CRM_Supportcase_Utils_EmailPrefillFields {

  /**
   * Get prefill fields for 'send new email forms'
   *
   * @param $caseSubject
   * @param $toContactId
   * @param $caseCategoryId
   * @return array
   */
  public static function get($caseSubject, $toContactId, $caseCategoryId) {
    $data = [
      'subject' => $caseSubject,
      'from_email_id' => '',
      'to_email_id' => '',
      'cc_email_ids' => '',
      'email_body' => '',
    ];

    $mailUtilsSetting = self::getFirstRelatedMailutilsSetting($caseCategoryId);
    if (!empty($mailUtilsSetting)) {
      $data['email_body'] = self::getBody($mailUtilsSetting);

      $mainEmail = CRM_Supportcase_Utils_Activity::getMainEmailIdByFromEmailAddressId($mailUtilsSetting['from_email_address_id']);
      if (!empty($mainEmail)) {
        $data['from_email_id'] = $mainEmail;
      }
    }

    if (!empty($toContactId)) {
      $toEmailData = CRM_Supportcase_Utils_EmailSearch::getEmailIdByContactId($toContactId);
      if (!empty($toEmailData)) {
        $data['to_email_id'] = $toEmailData['id'];
      }
    }

    return $data;
  }

  /**
   * @return string
   */
  private static function getBody($mailUtilsSetting) {
    $renderedTemplate = self::getRenderedTemplate($mailUtilsSetting);
    $messageNewLines = "\n\n";

    return "{$messageNewLines}{$renderedTemplate}\n\n";
  }

  /**
   * @param $mailUtilsSetting
   * @return string
   */
  private static function getRenderedTemplate($mailUtilsSetting) {
    if (empty($mailUtilsSetting['mailutils_template_id'])) {
      return '';
    }

    $mailutilsTemplate = \Civi\Api4\MailutilsTemplate::get(FALSE)
      ->addWhere('id', '=', $mailUtilsSetting['mailutils_template_id'])
      ->setLimit(1)
      ->execute()
      ->first();

    if (empty($mailutilsTemplate)) {
      return '';
    }

    return $mailutilsTemplate['message'];
  }

  /**
   * @param $caseCategoryId
   * @return false|array
   */
  private static function getFirstRelatedMailutilsSetting($caseCategoryId) {
    if (empty($caseCategoryId)) {
      return false;
    }

    $mailutilsSetting = \Civi\Api4\MailutilsSetting::get()
      ->addSelect('*')
      ->addWhere('support_case_category_id', '=', $caseCategoryId)
      ->addOrderBy('id', 'ASC')
      ->setLimit(1)
      ->execute()
      ->first();

    if (empty($mailutilsSetting)) {
      return false;
    }

    return $mailutilsSetting;
  }

}
