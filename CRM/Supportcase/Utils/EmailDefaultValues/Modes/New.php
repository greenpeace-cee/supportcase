<?php

class CRM_Supportcase_Utils_EmailDefaultValues_Modes_New extends CRM_Supportcase_Utils_EmailDefaultValues_Modes_Base {

  public function getValues() {
    $defaultValues = $this->getDefaultFields();

    $case = $this->getCase();
    $firstClientId = CRM_Supportcase_Utils_Case::getFirstClient($case);

    $defaultValues['case_category_id'] = $case['case_category_id'];
    $defaultValues['token_contact_id'] = $firstClientId;
    $defaultValues['subject'] = $case['subject'];

    $mailUtilsSetting = $this->getFirstRelatedMailutilsSetting($case['case_category_id']);
    if (!empty($mailUtilsSetting)) {
      $emailBody = $this->getBodyForNewEmail($mailUtilsSetting, $firstClientId);

      $mainEmail = CRM_Supportcase_Utils_Activity::getMainEmailIdByFromEmailAddressId($mailUtilsSetting['from_email_address_id']);
      if (!empty($mainEmail)) {
        $defaultValues['fromEmails'] = $mainEmail;
        $emailBody = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens($emailBody, $firstClientId);
      }

      $defaultValues['body'] = json_encode(['html' => $emailBody, 'text' => CRM_Utils_String::htmlToText($emailBody)]);
    }

    if (!empty($firstClientId)) {
      $toEmailData = CRM_Supportcase_Utils_EmailSearch::getEmailIdByContactId($firstClientId);
      if (!empty($toEmailData)) {
        $defaultValues['toEmails'] = $toEmailData['id'];
      }
    }

    return $defaultValues;
  }

  /**
   * @return string
   */
  protected function getBodyForNewEmail($mailUtilsSetting, $toContactId) {
    $renderedTemplate = CRM_Supportcase_Utils_SupportcaseTokenProcessor::handleTokens(
      $this->getRenderedTemplate($mailUtilsSetting),
      $toContactId
    );
    $message = "{$renderedTemplate}\n\n";

    return nl2br($message);
  }

  /**
   * @param $mailUtilsSetting
   * @return string
   */
  protected function getRenderedTemplate($mailUtilsSetting) {
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

}
